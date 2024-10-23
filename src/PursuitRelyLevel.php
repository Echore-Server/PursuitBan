<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

enum PursuitRelyLevel: int {

	case COMPLETELY = 2;

	/**
	 * Recommended
	 */
	case MEDIUM = 1;
	case NONE = 0;

	public function higherThanOrEqual(PursuitRelyLevel $level): bool {
		return $this->value >= $level->value;
	}
}
