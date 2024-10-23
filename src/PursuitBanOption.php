<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

readonly class PursuitBanOption {

	public string $executor;

	public string $reason;

	public ?int $expiresAt;

	public array $customData;

	public function __construct(string $executor, string $reason, ?int $expiresAt, array $customData) {
		$this->executor = $executor;
		$this->reason = $reason;
		$this->expiresAt = $expiresAt;
		$this->customData = $customData;
	}


}
