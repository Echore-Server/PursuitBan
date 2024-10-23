<?php

declare(strict_types=1);

namespace Echore\PursuitBan\listener;

use Echore\PursuitBan\PursuitBan;
use Echore\PursuitBan\PursuitCheck;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Server;

class PursuitListener implements Listener {

	public function onLogin(PlayerLoginEvent $event): void {
		$check = PursuitBan::getInstance()->check($event->getPlayer(), fn() => null);
		$check->getOnException()->add(function(PursuitCheck $check): void {
			Server::getInstance()->getLogger()->error("Pursuit: failed to check player");
			Server::getInstance()->getLogger()->logException($check->getException());
		});
	}
}
