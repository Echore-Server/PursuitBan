<?php

declare(strict_types=1);

namespace Echore\PursuitBan\form;

use Echore\PursuitBan\data\PursuitBanData;
use Echore\PursuitBan\PursuitBan;
use Echore\PursuitBan\PursuitUnBanRequest;
use pjz9n\advancedform\button\Button;
use pjz9n\advancedform\menu\MenuForm;
use pjz9n\advancedform\menu\response\MenuFormResponse;
use pocketmine\player\Player;

class PursuitFormConfirmUnBan extends MenuForm {

	public function __construct(private readonly int $id, PursuitBanData $data) {
		$clientData = $data->getClientData();
		$make = function(string $relyColor, string $firstKey, string $secondKey, string $content): string {
			return "{$relyColor}[!] §r§7$firstKey §8/ §7{$secondKey}§7: §f{$content}§f";
		};
		parent::__construct(
			"Pursuit UnBAN Confirmation",
			join("\n", [
				"§l§bUnBAN を実行します。よろしいですか？ / UnBan confirmation§r",
				$make("§a", "プレイヤー名", "Player name", $clientData->getPlayerName()),
				$make("§a", "プレイヤーUUID", "Player UUID", $clientData->getPlayerUuid()->toString()),
				$make("§a", "XUID", "XUID", $clientData->getXuid()),
				$make("§e", "デバイスID", "Device ID", $clientData->getDeviceId()->toString()),
				$make("§e", "PlayFab ID", "PlayFab ID", $clientData->getPlayFabId()),
				$make("§c", "クライアントランダムID", "Client Random ID", (string) $clientData->getClientRandomId()),
				$make("§c", "自己署名ID (JWT)", "Self-Signed ID (JWT)", $clientData->getSelfSignedId()->toString())
			]),
			[
				new Button("§a実行 / Execute UnBAN", name: "execute"),
				new Button("§cキャンセル / Cancel", name: "cancel")
			]
		);
	}

	protected function handleSelect(Player $player, MenuFormResponse $response): void {
		$buttonName = $response->getSelectedButton()->getName();

		$data = PursuitBan::getInstance()->getBanRepository()->get($this->id);

		if ($data === null || !$data->isActive()) {
			$player->sendMessage(PursuitBan::prefix("UnBAN に失敗 / UnBAN failed"));

			return;
		}

		if ($buttonName === "execute") {
			$unbanRequest = PursuitUnBanRequest::all($data->getClientData()->getPlayerName(), $data->getClientData()->getPlayerUuid());
			PursuitBan::getInstance()->requestUnBan($unbanRequest, function(array $targets) use ($player): void {
				if (!$player->isOnline()) {
					return;
				}

				$player->sendMessage(PursuitBan::prefix("UnBAN しました / UnBAN successfully executed"));
				$player->sendMessage(PursuitBan::prefix("該当BAN ID / Target BAN ID: " . join(", ", array_keys($targets))));
			});

			$player->sendMessage(PursuitBan::prefix("UnBAN をリクエストしました / Requested UnBAN"));
		} elseif ($buttonName === "cancel") {
			$player->sendMessage(PursuitBan::prefix("キャンセルしました / Cancelled"));
		}
	}
}
