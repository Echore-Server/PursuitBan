<?php

declare(strict_types=1);

namespace Echore\PursuitBan\revoker;

use Echore\PursuitBan\PursuitUnBanRequest;

interface PursuitRevoker {

	public function getName(): string;

	public function revoke(PursuitUnBanRequest $request): void;
}
