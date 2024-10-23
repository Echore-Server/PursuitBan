<?php

declare(strict_types=1);

namespace Echore\PursuitBan\command;

use Echore\PursuitBan\form\PursuitFormBan;
use Echore\PursuitBan\form\PursuitFormUnBan;
use Echore\PursuitBan\PursuitBan;
use Echore\PursuitBan\PursuitBanOption;
use Echore\PursuitBan\PursuitBanRequest;
use Echore\PursuitBan\PursuitBanRequestResult;
use Echore\PursuitBan\PursuitUnBanRequest;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class PursuitCommand extends Command implements PluginOwned {
	public function __construct(private readonly Plugin $plugin) {
		parent::__construct(
			"pursuit",
			"Pursuit commands"
		);

		$this->setPermission(DefaultPermissions::ROOT_OPERATOR);
	}

	public function getOwningPlugin(): Plugin {
		return $this->plugin;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) <= 0) {
			throw new InvalidCommandSyntaxException();
		}

		switch ($args[0]) {
			case "ban":
				if ($sender instanceof Player) {
					if (count($args) <= 1) {
						throw new InvalidCommandSyntaxException();
					}
					$form = new PursuitFormBan($args[1], $sender->getName());
					$sender->sendForm($form);
				} else {
					$this->executeBanConsole($sender, $args);
				}
				break;
			case "unban":
				if ($sender instanceof Player) {
					$form = new PursuitFormUnBan();
					$sender->sendForm($form);
				} else {
					$this->executeUnBanConsole($sender, $args);
				}
				break;
		}
	}

	protected function executeBanConsole(CommandSender $sender, array $args): void {
		if (count($args) <= 6) {
			throw new InvalidCommandSyntaxException();
		}

		$targetPlayerName = $args[1];
		$reason = $args[2];
		$executor = $args[3];
		$years = $args[4];
		if (ctype_digit($years)) {
			$years = (int) $years;
		} else {
			throw new InvalidCommandSyntaxException();
		}
		$days = $args[5];
		if (ctype_digit($days)) {
			$days = (int) $days;
		} else {
			throw new InvalidCommandSyntaxException();
		}
		$permanent = $args[6];
		if ($permanent === "true" || $permanent === "false") {
			$permanent = $permanent === "true";
		} elseif (ctype_digit($permanent)) {
			$permanent = (bool) $permanent;
		} else {
			throw new InvalidCommandSyntaxException();
		}

		$expireAt = $permanent ? null : (time() + $years * 31536000 + $days * 86400);

		$request = PursuitBanRequest::withPlayerName(
			$targetPlayerName, new PursuitBanOption(
				$executor,
				$reason,
				$expireAt,
				[]
			)
		);

		$result = PursuitBan::getInstance()->requestBan($request, function() use ($sender): void {
			$sender->sendMessage(PursuitBan::prefix("Ban が実行されました / BAN successfully executed"));
		});

		if ($result instanceof PursuitBanRequestResult) {
			$sender->sendMessage(PursuitBan::prefix("Ban に失敗しました / BAN failed: {$result->getStatus()}"));
		} else {
			$sender->sendMessage(PursuitBan::prefix("Ban をリクエストしました / Requested ban"));
		}
	}

	protected function executeUnBanConsole(CommandSender $sender, array $args): void {
		if (count($args) <= 1) {
			throw new InvalidCommandSyntaxException();
		}

		$targetPlayerName = $args[1];
		$request = PursuitUnBanRequest::withPlayerName($targetPlayerName);

		PursuitBan::getInstance()->requestUnBan($request, function(array $targets) use ($sender): void {
			$sender->sendMessage(PursuitBan::prefix("UnBAN しました / UnBAN successfully executed"));
			$sender->sendMessage(PursuitBan::prefix("該当BAN ID / Target BAN ID: " . join(", ", array_keys($targets))));
		});

		$sender->sendMessage(PursuitBan::prefix("UnBAN をリクエストしました / Requested UnBAN"));
	}

}
