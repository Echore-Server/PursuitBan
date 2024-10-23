<?php

declare(strict_types=1);

namespace Echore\PursuitBan;

use Closure;
use Echore\PursuitBan\data\PursuitBanData;
use Echore\PursuitBan\data\PursuitClientData;
use Echore\PursuitBan\executor\PursuitExecutor;
use Echore\PursuitBan\judger\PursuitJudger;
use Echore\PursuitBan\listener\PursuitListener;
use Echore\PursuitBan\provider\PursuitProvider;
use Echore\PursuitBan\repository\PursuitBanRepository;
use Echore\PursuitBan\revoker\PursuitRevoker;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;

final class PursuitBan {
	use SingletonTrait;

	private PursuitBanRepository $banRepository;

	/**
	 * @var PursuitJudger[]
	 */
	private array $judgers;

	/**
	 * @var PursuitProvider[]
	 */
	private array $providers;

	/**
	 * @var PursuitExecutor[]
	 */
	private array $executors;

	/**
	 * @var PursuitRevoker[]
	 */
	private array $revokers;

	private ?string $kickDisconnectScreenMessage;

	private ?string $kickQuitMessage;

	private bool $kickEnabled;

	private PursuitListener $listener;

	public function __construct(string $banRepositoryConfigPath) {
		$this->banRepository = new PursuitBanRepository(new Config($banRepositoryConfigPath, Config::JSON));
		$this->judgers = [];
		$this->providers = [];
		$this->executors = [];
		$this->revokers = [];

		$this->kickQuitMessage = null;
		$this->kickDisconnectScreenMessage = null;
		$this->kickEnabled = true;

		$this->listener = new PursuitListener();
	}

	public static function prefix(string $message): string {
		return "§tPursuit§8: " . $message;
	}

	public static function init(PluginBase $plugin): void {
		self::setInstance(new self(Path::join($plugin->getDataFolder(), "ban_repository.json")));
	}

	public function getListener(): PursuitListener {
		return $this->listener;
	}

	public function isKickEnabled(): bool {
		return $this->kickEnabled;
	}

	public function setKickEnabled(bool $kickEnabled): void {
		$this->kickEnabled = $kickEnabled;
	}

	public function getKickDisconnectScreenMessage(): ?string {
		return $this->kickDisconnectScreenMessage;
	}

	public function setKickDisconnectScreenMessage(?string $kickDisconnectScreenMessage): void {
		$this->kickDisconnectScreenMessage = $kickDisconnectScreenMessage;
	}

	public function getKickQuitMessage(): ?string {
		return $this->kickQuitMessage;
	}

	public function setKickQuitMessage(?string $kickQuitMessage): void {
		$this->kickQuitMessage = $kickQuitMessage;
	}

	public function registerJudger(PursuitJudger $judger): void {
		$this->judgers[spl_object_hash($judger)] = $judger;
	}

	public function registerProvider(PursuitProvider $provider): void {
		$this->providers[spl_object_hash($provider)] = $provider;
	}

	public function registerExecutor(PursuitExecutor $executor): void {
		$this->executors[spl_object_hash($executor)] = $executor;
	}

	public function registerRevoker(PursuitRevoker $revoker): void {
		$this->revokers[spl_object_hash($revoker)] = $revoker;
	}

	/**
	 * @return PursuitJudger[]
	 */
	public function getJudgers(): array {
		return $this->judgers;
	}

	/**
	 * @return PursuitExecutor[]
	 */
	public function getExecutors(): array {
		return $this->executors;
	}

	/**
	 * @return PursuitProvider[]
	 */
	public function getProviders(): array {
		return $this->providers;
	}

	/**
	 * @return PursuitRevoker[]
	 */
	public function getRevokers(): array {
		return $this->revokers;
	}

	/**
	 * @param PursuitUnBanRequest $request
	 * @param Closure(array<int, PursuitBanData>): void $onRevoke
	 * @return void
	 */
	public function requestUnBan(PursuitUnBanRequest $request, Closure $onRevoke): void {
		foreach ($this->revokers as $revoker) {
			$revoker->revoke($request);
		}

		// todo: pend

		$targets = [];
		foreach ($this->banRepository->getCachedActive() as $id => $data) {
			if (
				$data->getClientData()->getPlayerName() === $request->playerName ||
				$data->getClientData()->getPlayerUuid() === $request->playerUuid
			) {
				$data->setRevoked(true);
				$this->banRepository->update($id, $data);
				$targets[$id] = $data;
			}
		}

		($onRevoke)($targets);
	}

	/**
	 * @param Player $player
	 * @param Closure(PursuitCheck): void $onJudge
	 * @return PursuitCheck
	 */
	public function check(Player $player, Closure $onJudge): PursuitCheck {
		$check = new PursuitCheck($player, $this);
		$check->setQuitMessage($this->kickQuitMessage);
		$check->setDisconnectScreenMessage($this->kickDisconnectScreenMessage);
		$check->setKickEnabled($this->kickEnabled);
		$check->start($onJudge);

		return $check;
	}

	/**
	 * @param PursuitBanRequest $request
	 * @param Closure(PursuitBanExecution): void $onExecute
	 * @return PursuitBanRequestResult|PursuitBanExecution
	 */
	public function requestBan(PursuitBanRequest $request, Closure $onExecute): PursuitBanRequestResult|PursuitBanExecution {
		$builder = PursuitClientData::builder();

		try {
			foreach ($this->providers as $provider) {
				$provider->provide($request, $builder);
			}
		} catch (PursuitException $e) {
			return new PursuitBanRequestResult(PursuitBanRequestResult::FAILED, $e);
		}

		$clientData = $builder->build();

		if ($clientData === null) {
			return new PursuitBanRequestResult(PursuitBanRequestResult::FAILED_BUILD_CLIENT_DATA, null);
		}

		$banData = new PursuitBanData(
			$clientData,
			$request->option->expiresAt,
			false,
			$request->option->executor,
			$request->option->reason,
			$request->option->customData
		);

		$execution = new PursuitBanExecution(
			$request,
			$banData,
			$this
		);

		$execution->start($onExecute);

		return $execution;
	}

	public function getBanRepository(): PursuitBanRepository {
		return $this->banRepository;
	}
}
