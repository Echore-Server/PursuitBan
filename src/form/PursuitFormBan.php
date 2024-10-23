<?php

declare(strict_types=1);

namespace Echore\PursuitBan\form;

use Echore\PursuitBan\PursuitBan;
use Echore\PursuitBan\PursuitBanOption;
use Echore\PursuitBan\PursuitBanRequest;
use Echore\PursuitBan\PursuitBanRequestResult;
use pjz9n\advancedform\custom\CustomForm;
use pjz9n\advancedform\custom\element\Input;
use pjz9n\advancedform\custom\element\Slider;
use pjz9n\advancedform\custom\element\Toggle;
use pjz9n\advancedform\custom\response\CustomFormResponse;
use pocketmine\player\Player;

class PursuitFormBan extends CustomForm {

	public function __construct(private string $targetPlayerName, string $executor) {
		parent::__construct(
			"Pursuit BAN",
			[
				new Input("理由 / Reason", name: "reason"),
				new Input("実行者 / Executor", default: $executor, name: "executor"),
				new Slider("期間: 年 / Duration: years", 0, 20, 1, name: "years"),
				new Slider("期間: 日 / Duration: days", 0, 364, 1, name: "days"),
				new Toggle("無期限 / Permanent", false, name: "permanent")
			]
		);
	}

	protected function handleSubmit(Player $player, CustomFormResponse $response): void {
		$reason = $response->getInputResult("reason")->getText();
		$executor = $response->getInputResult("executor")->getText();
		$years = $response->getSliderResult("years")->getInt();
		$days = $response->getSliderResult("days")->getInt();
		$permanent = $response->getToggleResult("permanent")->getValue();

		$expireAt = $permanent ? null : (time() + $years * 31536000 + $days * 86400);

		$request = PursuitBanRequest::withPlayerName(
			$this->targetPlayerName, new PursuitBanOption(
				$executor,
				$reason,
				$expireAt,
				[]
			)
		);

		$result = PursuitBan::getInstance()->requestBan($request, function() use ($player): void {
			if (!$player->isOnline()) {
				return;
			}

			$player->sendMessage(PursuitBan::prefix("Ban が実行されました / BAN successfully executed"));
		});

		if ($result instanceof PursuitBanRequestResult) {
			$player->sendMessage(PursuitBan::prefix("Ban に失敗しました / BAN failed: {$result->getStatus()}"));
		} else {
			$player->sendMessage(PursuitBan::prefix("Ban をリクエストしました / Requested ban"));
		}
	}
}
