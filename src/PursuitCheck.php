<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use Closure;
use Echore\PursuitBan\data\PursuitBanData;
use Echore\PursuitBan\data\PursuitClientData;
use Echore\PursuitBan\judger\PursuitJudger;
use pocketmine\player\Player;
use pocketmine\utils\ObjectSet;
use RuntimeException;
use Throwable;

class PursuitCheck {

	private readonly Player $player;

	private readonly PursuitClientData $clientData;

	private readonly PursuitBan $instance;

	private ?PursuitCheckFailureReason $failureReason;

	private ObjectSet $onJudge;

	private ObjectSet $onException;

	private array $pendingJudges;

	private bool $waitingPendingJudges;

	private bool $running;

	private ?string $quitMessage;

	private ?string $disconnectScreenMessage;

	private bool $kickEnabled;

	private ?Throwable $exception;

	private ?PursuitBanData $resultBanData;

	public function __construct(Player $player, PursuitBan $instance) {
		$this->player = $player;
		$this->instance = $instance;
		$this->failureReason = null;
		$this->pendingJudges = [];
		$this->running = false;
		$this->waitingPendingJudges = false;
		$this->quitMessage = null;
		$this->disconnectScreenMessage = null;
		$this->kickEnabled = true;
		$this->exception = null;
		$this->resultBanData = null;

		$this->onJudge = new ObjectSet();
		$this->onException = new ObjectSet();
	}

	public function isKickEnabled(): bool {
		return $this->kickEnabled;
	}

	public function setKickEnabled(bool $kickEnabled): void {
		$this->kickEnabled = $kickEnabled;
	}

	/**
	 * @return string|null
	 */
	public function getDisconnectScreenMessage(): ?string {
		return $this->disconnectScreenMessage;
	}

	/**
	 * @param string|null $disconnectScreenMessage
	 */
	public function setDisconnectScreenMessage(?string $disconnectScreenMessage): void {
		$this->disconnectScreenMessage = $disconnectScreenMessage;
	}

	/**
	 * @return string|null
	 */
	public function getQuitMessage(): ?string {
		return $this->quitMessage;
	}

	/**
	 * @param string|null $quitMessage
	 */
	public function setQuitMessage(?string $quitMessage): void {
		$this->quitMessage = $quitMessage;
	}


	/**
	 * @return PursuitBanData|null
	 */
	public function getResultBanData(): ?PursuitBanData {
		return $this->resultBanData;
	}

	/**
	 * @return ObjectSet<Closure(PursuitCheck, PursuitBanData|null): void>
	 */
	public function getOnJudge(): ObjectSet {
		return $this->onJudge;
	}

	/**
	 * @return ObjectSet<Closure(PursuitCheck): void>
	 */
	public function getOnException(): ObjectSet {
		return $this->onException;
	}

	/**
	 * @return array<string, PursuitJudger>
	 */
	public function getPendingJudges(): array {
		return $this->pendingJudges;
	}

	/**
	 * @return PursuitCheckFailureReason|null
	 */
	public function getFailureReason(): ?PursuitCheckFailureReason {
		return $this->failureReason;
	}

	public function getClientData(): PursuitClientData {
		$this->checkRunning();

		return $this->clientData;
	}

	private function checkRunning(): void {
		if (!$this->running) {
			throw new RuntimeException("Not running");
		}
	}

	public function isRunning(): bool {
		return $this->running;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function start(Closure $onJudge): void {
		if ($this->running) {
			throw new RuntimeException("Already started");
		}
		$this->onJudge->add($onJudge);

		$this->running = true;
		$this->waitingPendingJudges = false;

		try {
			$this->clientData = PursuitClientData::from($this->player->getPlayerInfo());
			foreach ($this->instance->getJudgers() as $judger) {
				$judger->judge($this, $this->instance->getBanRepository());
			}

			if (count($this->pendingJudges) === 0) {
				$this->onFinished();
			} else {
				$this->waitingPendingJudges = true;
			}
		} catch (PursuitException $e) {
			$this->exception = $e;

			$this->onFinished();
		}
	}

	protected function onFinished(): void {
		foreach (($this->exception === null ? $this->onJudge : $this->onException) as $hook) {
			($hook)($this, $this->resultBanData);
		}

		if ($this->exception === null && $this->isFailed() && $this->kickEnabled) {
			$this->player->kick(
				"Pursuit Judger: " . $this->failureReason->getReason(),
				$this->quitMessage,
				$this->disconnectScreenMessage
			);
		}

		$this->running = false;
		$this->waitingPendingJudges = false;
	}

	public function isFailed(): bool {
		return $this->failureReason !== null;
	}

	/**
	 * @return Throwable|null
	 */
	public function getException(): ?Throwable {
		return $this->exception;
	}

	public function pend(PursuitJudger $judger): void {
		$this->checkRunning();
		$hash = spl_object_hash($judger);
		if (isset($this->pendingJudges[$hash])) {
			throw new RuntimeException("Judge {$judger->getName()} is already pending");
		}

		$this->pendingJudges[$hash] = $judger;
	}

	public function settle(PursuitJudger $judger): void {
		$this->checkRunning();
		$hash = spl_object_hash($judger);
		if (!isset($this->pendingJudges[$hash])) {
			throw new RuntimeException("Judge {$judger->getName()} is not pending");
		}

		unset($this->pendingJudges[$hash]);

		if ($this->waitingPendingJudges && count($this->pendingJudges) === 0) {
			$this->onFinished();
		}
	}

	public function isPending(PursuitJudger $judger): bool {
		return isset($this->pendingJudges[spl_object_hash($judger)]);
	}

	public function fail(PursuitBanData $result, string $reason): void {
		$this->checkRunning();
		if ($this->resultBanData !== null) {
			return;
		}
		$this->resultBanData = $result;
		$this->failureReason = new PursuitCheckFailureReason($reason);
	}
}
