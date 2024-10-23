<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use Throwable;

class PursuitBanRequestResult {
	const FAILED_BUILD_CLIENT_DATA = 0;
	const FAILED = 1;

	private int $status;

	private ?Throwable $exception;

	public function __construct(int $status, ?Throwable $exception) {
		$this->status = $status;
		$this->exception = $exception;
	}

	public function getStatus(): int {
		return $this->status;
	}

	public function getException(): ?Throwable {
		return $this->exception;
	}


}
