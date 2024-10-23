<?php

declare(strict_types=1);

namespace Echore\PursuitBan\judger;

use Echore\PursuitBan\PursuitCheck;
use Echore\PursuitBan\PursuitRelyLevel;
use Echore\PursuitBan\repository\PursuitBanRepository;

readonly class PursuitJudgerClientDataIntersects implements PursuitJudger {

	public function __construct(
		private PursuitRelyLevel $relyLevel
	) {
	}

	public function getName(): string {
		return "pursuit:client_data_intersects";
	}

	public function judge(PursuitCheck $check, PursuitBanRepository $repository): void {
		foreach ($repository->getCachedActive() as $id => $data) {
			if (!$data->isActive()) {
				$repository->update($id, null);
				continue;
			}

			if ($check->getClientData()->intersects($data->getClientData(), $this->relyLevel)) {
				$check->fail("Client data intersects");
				break;
			}
		}
	}
}
