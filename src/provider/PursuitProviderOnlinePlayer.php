<?php

declare(strict_types=1);

namespace Echore\PursuitBan\provider;

use Echore\PursuitBan\data\PursuitClientData;
use Echore\PursuitBan\data\PursuitClientDataBuilder;
use Echore\PursuitBan\PursuitBanRequest;

readonly class PursuitProviderOnlinePlayer implements PursuitProvider {

	public function getName(): string {
		return "pursuit:online_player";
	}

	public function provide(PursuitBanRequest $request, PursuitClientDataBuilder $result): void {
		$player = $request->fetchOnlinePlayer();

		if ($player === null) {
			return;
		}

		$data = PursuitClientData::from($player->getPlayerInfo());

		$result
			->setPlayerName($data->getPlayerName())
			->setPlayerUuid($data->getPlayerUuid())
			->setXuid($data->getXuid())
			->setDeviceId($data->getDeviceId())
			->setPlayFabId($data->getPlayFabId())
			->setClientRandomId($data->getClientRandomId())
			->setSelfSignedId($data->getSelfSignedId());
	}
}
