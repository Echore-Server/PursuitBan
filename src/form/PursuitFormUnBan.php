<?php

declare(strict_types=1);

namespace Echore\PursuitBan\form;

use Echore\PursuitBan\PursuitBan;
use pjz9n\advancedform\custom\CustomForm;
use pjz9n\advancedform\custom\element\Dropdown;
use pjz9n\advancedform\custom\element\SelectorOption;
use pjz9n\advancedform\custom\response\CustomFormResponse;
use pocketmine\player\Player;

class PursuitFormUnBan extends CustomForm {

	private array $options;

	public function __construct() {
		$this->options = [];

		foreach (PursuitBan::getInstance()->getBanRepository()->getCachedActive() as $id => $data) {
			if (!$data->isActive()) {
				continue;
			}

			$this->options[] = new SelectorOption("{$data->getClientData()->getPlayerName()}\n{$data->getReason()}", value: $data, name: (string) $id);
		}

		parent::__construct(
			"Pursuit UnBAN"
		);

		if (count($this->options) > 0) {
			$this->appendElement(new Dropdown("対象 / Target", $this->options, name: "target"));
		} else {
			$this->appendMessage("§cBAN されているプレイヤーがいません / No players have been banned");
		}
	}

	protected function handleSubmit(Player $player, CustomFormResponse $response): void {
		if (count($this->options) > 0) {
			$id = (int) $response->getSelectorResult("target")->getOptionName();
			$data = $response->getSelectorResult("target")->getOptionValue();

			$form = new PursuitFormConfirmUnBan($id, $data);
			$player->sendForm($form);
		}
	}

}
