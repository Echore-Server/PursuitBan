<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use pocketmine\player\Player;
use pocketmine\Server;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

readonly final class PursuitBanRequest {

	private function __construct(
		public ?string          $playerName,
		public ?UuidInterface   $playerUuid,
		public PursuitBanOption $option
	) {
	}

	public static function withPlayerName(string $playerName, PursuitBanOption $option): self {
		return new self($playerName, null, $option);
	}

	public static function withPlayerUuid(UuidInterface $playerUuid, PursuitBanOption $option): self {
		return new self(null, $playerUuid, $option);
	}

	public static function all(string $playerName, UuidInterface $playerUuid, PursuitBanOption $option): self {
		return new self($playerName, $playerUuid, $option);
	}

	public function fetchOnlinePlayer(): ?Player {
		$server = Server::getInstance();

		if ($this->playerUuid !== null) {
			$player = $server->getPlayerByUUID($this->playerUuid);
		} elseif ($this->playerName !== null) {
			$player = $server->getPlayerExact($this->playerName);
		} else {
			throw new RuntimeException("Invalid request");
		}

		return $player;
	}
}
