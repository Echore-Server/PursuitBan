<?php

declare(strict_types=1);

namespace Echore\PursuitBan\executor;

use Echore\PursuitBan\PursuitBanExecution;

interface PursuitExecutor {

	public function getName(): string;

	public function execute(PursuitBanExecution $execution): void;

}
