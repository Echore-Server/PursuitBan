<?php

declare(strict_types=1);

namespace Echore\PursuitBan\data;

use Ramsey\Uuid\UuidInterface;

final class PursuitClientDataBuilder {
	
	private ?string $playerName;

	private ?UuidInterface $playerUuid;

	private ?string $xuid;

	private ?UuidInterface $deviceId;

	private ?string $playFabId;

	private ?int $clientRandomId;

	private ?UuidInterface $selfSignedId;

	public function __construct() {
		$this->playerName = null;
		$this->playerUuid = null;
		$this->xuid = null;
		$this->deviceId = null;
		$this->playFabId = null;
		$this->clientRandomId = null;
		$this->selfSignedId = null;
	}

	public function build(): ?PursuitClientData {
		if (
			$this->playerName === null ||
			$this->playerUuid === null ||
			$this->deviceId === null ||
			$this->playFabId === null ||
			$this->clientRandomId === null ||
			$this->selfSignedId === null
		) {
			return null;
		}

		return new PursuitClientData(
			$this->playerName,
			$this->playerUuid,
			$this->xuid,
			$this->deviceId,
			$this->playFabId,
			$this->clientRandomId,
			$this->selfSignedId
		);
	}

	public function getPlayerName(): ?string {
		return $this->playerName;
	}

	public function setPlayerName(string $playerName): PursuitClientDataBuilder {
		$this->playerName = $playerName;

		return $this;
	}

	public function getPlayerUuid(): ?UuidInterface {
		return $this->playerUuid;
	}

	public function setPlayerUuid(UuidInterface $playerUuid): PursuitClientDataBuilder {
		$this->playerUuid = $playerUuid;

		return $this;
	}

	public function getXuid(): ?string {
		return $this->xuid;
	}

	public function setXuid(?string $xuid): PursuitClientDataBuilder {
		$this->xuid = $xuid;

		return $this;
	}

	public function getDeviceId(): ?UuidInterface {
		return $this->deviceId;
	}

	public function setDeviceId(UuidInterface $deviceId): PursuitClientDataBuilder {
		$this->deviceId = $deviceId;

		return $this;
	}

	public function getPlayFabId(): ?string {
		return $this->playFabId;
	}

	public function setPlayFabId(string $playFabId): PursuitClientDataBuilder {
		$this->playFabId = $playFabId;

		return $this;
	}

	public function getClientRandomId(): ?int {
		return $this->clientRandomId;
	}

	public function setClientRandomId(int $clientRandomId): PursuitClientDataBuilder {
		$this->clientRandomId = $clientRandomId;

		return $this;
	}

	public function getSelfSignedId(): ?UuidInterface {
		return $this->selfSignedId;
	}

	public function setSelfSignedId(UuidInterface $selfSignedId): PursuitClientDataBuilder {
		$this->selfSignedId = $selfSignedId;

		return $this;
	}


}
