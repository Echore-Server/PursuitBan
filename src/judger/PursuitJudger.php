<?php

declare(strict_types=1);

namespace Echore\PursuitBan\judger;

use Echore\PursuitBan\PursuitCheck;
use Echore\PursuitBan\repository\PursuitBanRepository;

interface PursuitJudger {

	public function getName(): string;

	public function judge(PursuitCheck $check, PursuitBanRepository $repository): void;
}
