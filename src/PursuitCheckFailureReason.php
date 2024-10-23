<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

readonly class PursuitCheckFailureReason {

	private string $reason;

	public function __construct(
		string $reason
	) {
		$this->reason = $reason;
	}

	public function getReason(): string {
		return $this->reason;
	}

}
