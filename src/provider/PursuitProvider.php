<?php

declare(strict_types=1);

namespace Echore\PursuitBan\provider;

use Echore\PursuitBan\data\PursuitClientDataBuilder;
use Echore\PursuitBan\PursuitBanRequest;

interface PursuitProvider {

	public function getName(): string;

	public function provide(PursuitBanRequest $request, PursuitClientDataBuilder $result): void;
}
