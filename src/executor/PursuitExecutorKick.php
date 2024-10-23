<?php

declare(strict_types=1);

namespace Echore\PursuitBan\executor;

use Echore\PursuitBan\PursuitBanExecution;

readonly class PursuitExecutorKick implements PursuitExecutor {

	public function __construct(
		private ?string $reason,
		private ?string $quitMessage,
		private ?string $disconnectScreenMessage
	) {
	}

	public function getName(): string {
		return "pursuit:kick";
	}

	public function execute(PursuitBanExecution $execution): void {
		$execution->getOnExecute()->add(function(PursuitBanExecution $execution): void {
			$player = $execution->getRequest()->fetchOnlinePlayer();

			if ($player === null) {
				return;
			}

			$player->kick(
				$this->reason,
				$this->quitMessage,
				$this->disconnectScreenMessage
			);
		});
	}
}
