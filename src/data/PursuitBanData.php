<?php

declare(strict_types=1);

namespace Echore\PursuitBan\data;

use http\Exception\InvalidArgumentException;
use JsonSerializable;
use stdClass;

class PursuitBanData implements JsonSerializable {

	private readonly PursuitClientData $clientData;

	private ?int $expiresAt;

	private bool $revoked;

	private string $executor;

	private string $reason;

	private array $customData;

	public function __construct(PursuitClientData $clientData, ?int $expiresAt, bool $revoked, string $executor, string $reason, array $customData) {
		$this->clientData = $clientData;
		$this->expiresAt = $expiresAt;
		$this->revoked = $revoked;
		$this->executor = $executor;
		$this->reason = $reason;
		$this->customData = $customData;
	}

	public static function deserialize(array $json): self {
		return new self(
			PursuitClientData::deserialize($json["client_data"]),
			$json["expires_at"],
			$json["revoked"],
			$json["executor"],
			$json["reason"],
			$json["custom_data"]
		);
	}

	public function getCustomData(): array {
		return $this->customData;
	}

	public function setCustomData(array $customData): PursuitBanData {
		$this->customData = $customData;

		return $this;
	}

	public function addCustomData(string $key, mixed $value): PursuitBanData {
		if (is_object($value) && !$value instanceof JsonSerializable && !$value instanceof stdClass) {
			throw new InvalidArgumentException("Value is not json serializable");
		}

		$this->customData[$key] = $value;

		return $this;
	}

	public function getClientData(): PursuitClientData {
		return $this->clientData;
	}

	public function getExecutor(): string {
		return $this->executor;
	}

	public function setExecutor(string $executor): PursuitBanData {
		$this->executor = $executor;

		return $this;
	}

	public function getExpiresAt(): ?int {
		return $this->expiresAt;
	}

	public function setExpiresAt(?int $expiresAt): PursuitBanData {
		$this->expiresAt = $expiresAt;

		return $this;
	}

	public function isActive(): bool {
		return !$this->isExpired() && !$this->isRevoked();
	}

	public function isExpired(?int $time = null): bool {
		return $this->expiresAt !== null && ($time ?? time()) >= $this->expiresAt;
	}

	public function isRevoked(): bool {
		return $this->revoked;
	}

	public function setRevoked(bool $revoked): PursuitBanData {
		$this->revoked = $revoked;

		return $this;
	}

	public function isPermanent(): bool {
		return $this->expiresAt === null;
	}

	public function getReason(): string {
		return $this->reason;
	}

	public function setReason(string $reason): PursuitBanData {
		$this->reason = $reason;

		return $this;
	}

	public function jsonSerialize(): array {
		return [
			"client_data" => $this->clientData->jsonSerialize(),
			"expires_at"  => $this->expiresAt,
			"revoked"     => $this->revoked,
			"executor"    => $this->executor,
			"reason"      => $this->reason,
			"custom_data" => $this->customData
		];
	}
}
