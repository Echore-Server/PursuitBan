<?php

declare(strict_types=1);

namespace Echore\PursuitBan\data;

use Echore\PursuitBan\PursuitException;
use Echore\PursuitBan\PursuitRelyLevel;
use JsonSerializable;
use pocketmine\player\PlayerInfo;
use pocketmine\player\XboxLivePlayerInfo;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly final class PursuitClientData implements JsonSerializable {

	/**
	 * The following values are reliable (cannot modify)
	 */
	private string $playerName;

	private UuidInterface $playerUuid;

	private ?string $xuid;

	/**
	 * The following values are not reliable (modifiable by cheating jwt)
	 */
	private UuidInterface $deviceId;

	private string $playFabId;

	/**
	 * DO NOT rely on the following values!!! (modifiable by modifying local files)
	 */
	private int $clientRandomId;

	private UuidInterface $selfSignedId;

	public function __construct(string $playerName, UuidInterface $playerUuid, ?string $xuid, UuidInterface $deviceId, string $playFabId, int $clientRandomId, UuidInterface $selfSignedId) {
		$this->playerName = $playerName;
		$this->playerUuid = $playerUuid;
		$this->xuid = $xuid;
		$this->deviceId = $deviceId;
		$this->playFabId = $playFabId;
		$this->clientRandomId = $clientRandomId;
		$this->selfSignedId = $selfSignedId;
	}

	public static function builder(): PursuitClientDataBuilder {
		return new PursuitClientDataBuilder();
	}

	public static function from(PlayerInfo $info): self {
		$getFromExtraData = function(string $key) use ($info): mixed {
			return $info->getExtraData()[$key] ?? throw new PursuitException("Extra data not exists (key: $key)");
		};

		try {
			return new self(
				$info->getUsername(),
				$info->getUuid(),
				$info instanceof XboxLivePlayerInfo ? $info->getXuid() : null,
				Uuid::fromString($getFromExtraData("DeviceId")), // "ID" spelling inconsistency wtf mojang
				$getFromExtraData("PlayFabId"),
				$getFromExtraData("ClientRandomId"),
				Uuid::fromString($getFromExtraData("SelfSignedId"))
			);
		} catch (UuidExceptionInterface $e) {
			throw new PursuitException("UUID decode failed", previous: $e);
		}
	}

	public function getXuid(): ?string {
		return $this->xuid;
	}

	public static function deserialize(array $json): self {
		return new self(
			$json["player_name"],
			Uuid::fromString($json["player_uuid"]),
			$json["xuid"],
			Uuid::fromString($json["device_id"]),
			$json["play_fab_id"],
			$json["client_random_id"],
			Uuid::fromString($json["self_signed_id"])
		);
	}

	public function intersects(PursuitClientData $with, PursuitRelyLevel $relyLevel): bool {
		$result = false;
		if (
			$this->playerName === $with->playerName ||
			$this->playerUuid === $with->playerUuid ||
			($this->xuid !== null && $this->xuid === $with->xuid)
		) {
			$result = true;
		}

		if (
			$relyLevel->higherThanOrEqual(PursuitRelyLevel::MEDIUM) &&
			(
				$this->deviceId->equals($with->deviceId) ||
				$this->playFabId === $with->playFabId
			)
		) {
			$result = true;
		}

		if (
			$relyLevel->higherThanOrEqual(PursuitRelyLevel::COMPLETELY) &&
			(
				$this->clientRandomId === $with->clientRandomId ||
				$this->selfSignedId === $with->selfSignedId
			)
		) {
			$result = true;
		}

		return $result;
	}

	public function getPlayerName(): string {
		return $this->playerName;
	}

	public function getPlayerUuid(): UuidInterface {
		return $this->playerUuid;
	}

	public function getDeviceId(): UuidInterface {
		return $this->deviceId;
	}

	public function getPlayFabId(): string {
		return $this->playFabId;
	}

	public function getClientRandomId(): int {
		return $this->clientRandomId;
	}

	public function getSelfSignedId(): UuidInterface {
		return $this->selfSignedId;
	}

	public function jsonSerialize(): array {
		return [
			"player_name"      => $this->playerName,
			"player_uuid"      => $this->playerUuid->toString(),
			"xuid"             => $this->xuid,
			"device_id"        => $this->deviceId->toString(),
			"play_fab_id"      => $this->playFabId,
			"client_random_id" => $this->clientRandomId,
			"self_signed_id"   => $this->selfSignedId
		];
	}
}
