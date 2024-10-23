<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use Echore\PursuitBan\command\PursuitCommand;
use Echore\PursuitBan\executor\PursuitExecutorKick;
use Echore\PursuitBan\judger\PursuitJudgerClientDataIntersects;
use Echore\PursuitBan\provider\PursuitProviderOnlinePlayer;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

	protected function onLoad(): void {
		PursuitBan::init($this);

		PursuitBan::getInstance()->registerProvider(new PursuitProviderOnlinePlayer());
		PursuitBan::getInstance()->registerExecutor(new PursuitExecutorKick("Banned", null, "Disconnected from server"));
		PursuitBan::getInstance()->registerJudger(new PursuitJudgerClientDataIntersects(PursuitRelyLevel::COMPLETELY));

		PursuitBan::getInstance()->setKickDisconnectScreenMessage("Disconnected from server");

		$this->getServer()->getCommandMap()->register("pursuit", new PursuitCommand($this));
	}

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents(PursuitBan::getInstance()->getListener(), $this);
	}

	protected function onDisable(): void {
		PursuitBan::getInstance()->getBanRepository()->save();
	}
}
