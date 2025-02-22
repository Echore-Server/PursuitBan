<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use Closure;
use Echore\PursuitBan\data\PursuitBanData;
use Echore\PursuitBan\executor\PursuitExecutor;
use pocketmine\utils\ObjectSet;
use RuntimeException;

class PursuitBanExecution {

	private readonly PursuitBanRequest $request;

	private readonly PursuitBanData $banData;

	private readonly PursuitBan $instance;

	private ObjectSet $onExecute;

	private array $pendingExecutions;

	private bool $waitingPendingExecutions;

	private bool $running;

	public function __construct(PursuitBanRequest $request, PursuitBanData $banData, PursuitBan $instance) {
		$this->request = $request;
		$this->banData = $banData;
		$this->instance = $instance;
		$this->pendingExecutions = [];
		$this->running = false;
		$this->waitingPendingExecutions = false;

		$this->onExecute = new ObjectSet();
	}

	public function isPending(PursuitExecutor $executor): bool {
		return isset($this->pendingExecutions[spl_object_hash($executor)]);
	}

	/**
	 * @return ObjectSet<Closure>
	 */
	public function getOnExecute(): ObjectSet {
		return $this->onExecute;
	}

	/**
	 * @return array<string, PursuitExecutor>
	 */
	public function getPendingExecutions(): array {
		return $this->pendingExecutions;
	}

	public function getRequest(): PursuitBanRequest {
		return $this->request;
	}

	public function getBanData(): PursuitBanData {
		return $this->banData;
	}

	public function start(Closure $onExecute): void {
		if ($this->running) {
			throw new RuntimeException("Already started");
		}
		$this->onExecute->add($onExecute);

		$this->running = true;
		$this->waitingPendingExecutions = false;

		foreach ($this->instance->getExecutors() as $executor) {
			$executor->execute($this);
		}

		if (count($this->pendingExecutions) === 0) {
			$this->onFinished();
		} else {
			$this->waitingPendingExecutions = true;
		}
	}

	protected function onFinished(): void {
		$this->instance->getBanRepository()->insert($this->banData);

		foreach ($this->onExecute as $hook) {
			($hook)($this);
		}

		$this->running = false;
		$this->waitingPendingExecutions = false;
	}

	public function pend(PursuitExecutor $executor): void {
		$this->checkRunning();
		$hash = spl_object_hash($executor);
		if (isset($this->pendingExecutions[$hash])) {
			throw new RuntimeException("Execution {$executor->getName()} is already pending");
		}

		$this->pendingExecutions[$hash] = $executor;
	}

	private function checkRunning(): void {
		if (!$this->running) {
			throw new RuntimeException("Not running");
		}
	}
	
	public function isRunning(): bool {
		return $this->running;
	}

	public function settle(PursuitExecutor $executor): void {
		$this->checkRunning();
		$hash = spl_object_hash($executor);
		if (!isset($this->pendingExecutions[$hash])) {
			throw new RuntimeException("Execution {$executor->getName()} is not pending");
		}

		unset($this->pendingExecutions[$hash]);

		if ($this->waitingPendingExecutions && count($this->pendingExecutions) === 0) {
			$this->onFinished();
		}
	}
}
