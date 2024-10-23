<?php

declare(strict_types=1);

namespace Echore\PursuitBan\repository;

use Echore\PursuitBan\data\PursuitBanData;
use pocketmine\utils\Config;

class PursuitBanRepository {

	private Config $config;

	private array $repository;

	private array $active;

	public function __construct(Config $config) {
		$this->config = $config;
		$config->setDefaults([
			"next_id" => 1
		]);
		$config->enableJsonOption(JSON_UNESCAPED_UNICODE);
		$config->enableJsonOption(JSON_UNESCAPED_SLASHES);
		$this->repository = [];
		$this->active = [];

		$this->reload();
	}

	public function reload(bool $reloadFromFile = false): void {
		if ($reloadFromFile) {
			$this->config->reload();
		}
		$this->repository = [];
		$this->active = [];
		foreach ($this->config->getAll() as $k => $raw) {
			if ($k === "next_id") {
				continue;
			}
			$this->repository[(int) $k] = $data = PursuitBanData::deserialize($raw);

			if ($data->isActive()) {
				$this->active[(int) $k] = $data;
			}
		}
	}

	/**
	 * @return array<int, PursuitBanData>
	 */
	public function getAll(): array {
		return $this->repository;
	}

	public function save(): void {
		$this->config->save();
	}

	/**
	 * @return array<int, PursuitBanData>
	 */
	public function getCachedActive(): array {
		return $this->active;
	}

	public function update(int $id, ?PursuitBanData $data): bool {
		if (isset($this->repository[$id])) {
			if ($data !== null) {
				$clonedData = clone $data;
				$this->repository[$id] = $clonedData;
				if ($data->isActive() && isset($this->active[$id])) {
					$this->active[$id] = $clonedData;
				}

				$this->config->set((string) $id, $data->jsonSerialize());
			} else {
				$data = $this->repository[$id];
			}

			if (!$data->isActive()) {
				unset($this->active[$id]);
			}

			return true;
		}

		return false;
	}

	public function insert(PursuitBanData $data): int {
		$id = $this->nextId();
		$this->config->set((string) $id, $data->jsonSerialize());

		$clonedData = clone $data;
		$this->repository[$id] = $clonedData;
		if ($data->isActive()) {
			$this->active[$id] = $clonedData;
		}

		return $id;
	}

	public function nextId(): int {
		$id = $this->config->get("next_id");
		$this->config->set("next_id", $id + 1);

		return $id;
	}

	public function get(int $id): ?PursuitBanData {
		if (isset($this->repository[$id])) {
			return clone $this->repository[$id];
		}

		return null;
	}

	public function remove(int $id): bool {
		if (isset($this->repository[$id])) {
			unset($this->repository[$id]);
			unset($this->active[$id]);
			$this->config->remove((string) $id);

			return true;
		}

		return false;
	}
}
