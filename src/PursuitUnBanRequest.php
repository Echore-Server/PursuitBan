<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use Ramsey\Uuid\UuidInterface;

readonly final class PursuitUnBanRequest {
	private function __construct(
		public ?string        $playerName,
		public ?UuidInterface $playerUuid,
	) {
	}

	public static function withPlayerName(string $playerName): self {
		return new self($playerName, null);
	}

	public static function withPlayerUuid(UuidInterface $playerUuid): self {
		return new self(null, $playerUuid);
	}

	public static function all(string $playerName, UuidInterface $playerUuid): self {
		return new self($playerName, $playerUuid);
	}
}
