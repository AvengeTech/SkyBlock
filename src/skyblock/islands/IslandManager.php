<?php

namespace skyblock\islands;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\{
	World,
	WorldCreationOptions,
	format\Chunk
};
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;
use pocketmine\world\generator\{
	GeneratorManager,
	InvalidGeneratorOptionsException
};

use skyblock\{
	SkyBlock,
	SkyBlockPlayer,
};
use skyblock\islands\permission\{
	Permissions,
	PlayerPermissions
};
use skyblock\islands\world\generator\{
	GeneratorInfo,
};
use skyblock\islands\world\provider\IslandWorldProvider;
use skyblock\settings\SkyBlockSettings;

use core\Core;
use core\network\protocol\PlayerLoadActionPacket;
use core\network\server\{
	ServerInstance,
	SubServer
};
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\{
	TextFormat,
	Utils
};

class IslandManager {

	const ISLANDS_LOCATION = "/[REDACTED]/skyblock/islands/";
	const SERVER_LOCATION = "/[REDACTED]/skyblock/";

	const ERROR_ALREADY_LOADED = 0;
	const ERROR_LOADED_ELSEWHERE = 1;
	const ERROR_NOT_IN_DATABASE = 2;
	const ERROR_WORLD_NOT_FOUND = 3;

	public bool $loadIslandsOnMain = false;

	public static int $islandBatchId = 0;
	public array $batchLoad = [];

	public array $islands = [];

	public array $generators = [];

	public function __construct() {
		$this->saveExistingIslandWorlds();
		$this->registerGenerators();
	}

	public function loadIslandsOnMain(): bool {
		//return true;
		//return !Core::thisServer()->isTestServer();
		return $this->loadIslandsOnMain;
	}

	public static function newIslandBatchId(): int {
		return self::$islandBatchId++;
	}

	public function getIslandBatch(int $batchId): ?IslandBatch {
		return $this->batchLoad[$batchId] ?? null;
	}

	/**
	 * Should only be used on startup
	 * Saves worlds that might still exist
	 * Due to crash
	 */
	public function saveExistingIslandWorlds(): void {
		/** @var SubServer $server */
		if ((($server = Core::thisServer())->isSubServer() && $server->getSubId() !== "pvp") || $this->loadIslandsOnMain()) {
			$worlds = scandir($dir = self::SERVER_LOCATION . ($server->isSubServer() ? $server->getSubId(true) : $server->getTypeId()) . "/worlds");
			foreach ($worlds as $world) {
				if (self::isIslandWorld($world)) {
					if (stristr($world, ".tar")) {
						@unlink($dir . "/" . $world);
					} else {
						$this->unloadIslandWorld($world);
					}
				}
			}
		}
	}

	public function tick(): void {
		/** @var SubServer $server */
		if (!($server = Core::thisServer())->isSubServer() || $server->getSubId() !== "pvp") {
			foreach ($this->getIslands() as $key => $island) {
				if (($this->isWorldLoadedHere($island->getWorldName())) && !$island->tick()) {
					$this->unloadIsland($island);
				}
			}
		}
	}

	public function close(): void {
		/** @var SubServer $server */
		if (!($server = Core::thisServer())->isSubServer() || $server->getSubId() !== "pvp") {
			foreach ($this->getIslands() as $key => $island) {
				$this->unloadIsland($island, $loaded = $this->isWorldLoadedHere($island->getWorldName()), $loaded, false);
			}
		}
	}

	public function saveAll(bool $async = false): void {
		/** @var SubServer $server */
		if (!($server = Core::thisServer())->isSubServer() || $server->getSubId() !== "pvp") {
			foreach ($this->getIslands() as $island) {
				if ($this->isWorldLoadedHere($island->getWorldName())) $island->save($async);
			}
		}
	}

	public function getIslands(): array {
		return $this->islands;
	}

	public function getIslandBy(string $world): ?Island {
		return $this->islands[$world] ?? null;
	}

	public static function isIslandWorld(string $name): bool {
		return count(explode("-", $name)) > 2;
	}

	public function createIsland(Player $player, string $name, int $type, \Closure $onCompletion, bool $keepLoaded = true): bool {
		/** @var SkyBlockPlayer $player */
		$worldName = $player->getXuid() . "-" . $type . "-" . time();
		if ($this->generateIslandWorld($worldName, $type, true)) {
			$permissions = new PlayerPermissions(
				$player->getUser(),
				$worldName,
				time(),
				Permissions::VERSION,
				Permissions::DEFAULT_INVITE_PERMISSIONS
			);
			foreach ($permissions->getPermissions() as $key => $perm) {
				$permissions->setPermission($key, true);
			}
			$permissions->setHierarchy(100);

			$genClass = $this->getGeneratorInfo($type)->getClass();
			$genClass = new $genClass(0, "");

			$island = new Island(
				$worldName,
				$type,
				time(),

				$name,
				"This is my brand new island!",

				1,
				-1,
				false,
				Permissions::VERSION,
				Permissions::DEFAULT_VISITOR_PERMISSIONS,
				Permissions::DEFAULT_INVITE_PERMISSIONS,
				[$permissions],
				[],
				$genClass->getWorldSpawn()
			);
			$island->getWorld()->requestChunkPopulation(0, 0, null)->onCompletion(function (Chunk $chunk) use ($island, $player, $worldName, $keepLoaded, $onCompletion, $permissions): void {
				if (!$player->isConnected()) {
					$this->unloadIslandWorld($worldName, false);
					return;
				}
				$island->setup($player);
				$island->save();

				($ises = $player->getGameSession()->getIslands())->addPermission($permissions);
				if (count($ises->getPermissions()) === 1) {
					$player->getGameSession()->getSettings()->setSetting(SkyBlockSettings::DEFAULT_ISLAND, $worldName);
				}

				if ($keepLoaded) {
					$this->islands[$worldName] = $island;
				} else {
					$this->unloadIslandWorld($worldName);
				}
				$onCompletion($island);
			}, function () use ($player, $worldName): void {
				if ($player->isConnected()) {
					$player->sendMessage(TextFormat::RI . "Unable to create an island at this time. Please try again later");
				}
				$this->unloadIslandWorld($worldName, false);
			});
			return true;
		}
		return false;
	}

	public function gotoIsland(Player $player, Island|string $worldName, bool $elsewhere = true, bool $findServer = true): void {
		/** @var SkyBlockPlayer $player */
		if ($player->getGameSession()->getCombat()->inCombat()) {
			$player->sendMessage(TextFormat::RI . "You cannot teleport to an island while in combat!");
			return;
		}

		$worldName = $worldName instanceof Island ? $worldName->getWorldName() : $worldName;
		/** @var SubServer $ts */
		if (!SkyBlock::pvpOnMain() && ($ts = Core::thisServer())->isSubServer() && $ts->getSubId() === "pvp") {
			($pk = new PlayerLoadActionPacket([
				"player" => $player->getName(),
				"server" => $ts->getParentServer()->getIdentifier(),
				"action" => "island",
				"actionData" => ["world" => $worldName]
			]))->queue();
			$player->gotoSpawn();
			return;
		}

		$loadFunction = function () use ($worldName, $player): void {
			$this->loadIsland($worldName, function (Island $island) use ($player): void {
				if (!$player->isConnected()) return;
				if (!$island->isBlocked($player) || $player->isStaff()) {
					if ($player->isStaff() || $island->isPublic() || $island->getPermissions()->getPermissionsBy($player) !== null) {
						$island->teleportTo($player);
					} else {
						$player->gotoSpawn(TextFormat::RI . "You no longer have permissions on this island!");
					}
				} else {
					$player->gotoSpawn(TextFormat::RI . "You are blocked from visiting this island!");
				}
			}, function (int $errorId) use ($worldName, $player): void {
				if (!$player->isConnected()) return;
				switch ($errorId) {
					case IslandManager::ERROR_ALREADY_LOADED:
						$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($worldName);
						if ($island instanceof Island) {
							if (!$island->isBlocked($player) || $player->isStaff()) {
								if ($player->isStaff() || $island->isPublic() || $island->getPermissions()->getPermissionsBy($player) !== null) {
									$island->teleportTo($player);
								} else {
									$player->gotoSpawn(TextFormat::RI . "You no longer have permissions on this island!");
								}
							} else {
								$player->gotoSpawn(TextFormat::RI . "You are blocked from visiting this island!");
							}
						} else {
							$player->gotoSpawn("Island was already loaded but also not found...?");
						}
						break;
					case IslandManager::ERROR_LOADED_ELSEWHERE:
						$server = SkyBlock::getInstance()->getIslands()->getIslandManager()->findIslandWorldServer($worldName);
						if ($server instanceof SubServer) {
							($pk = new PlayerLoadActionPacket([
								"player" => $player->getName(),
								"server" => $server->getIdentifier(),
								"action" => "island",
								"actionData" => ["world" => $worldName]
							]))->queue();
							SkyBlock::getInstance()->onQuit($player, true);
							//$server->transfer($player);
							$player->getGameSession()->save(true, function ($session) use ($player, $server): void {
								if ($player->isConnected()) {
									$server->transfer($player);
									$server->sendSessionSavedPacket($player, 1);
								}
								$player->getGameSession()->getSessionManager()->removeSession($player);
							});
							$player->sendMessage(TextFormat::YELLOW . "Saving game session data...");
						} else {
							$player->gotoSpawn("Island was loaded elsewhere but we dunno where...?");
						}
						break;
					case IslandManager::ERROR_NOT_IN_DATABASE:
					case IslandManager::ERROR_WORLD_NOT_FOUND:
						$player->gotoSpawn("Pieces of this island are missing. Cannot visit (" . $errorId . ")");
						break;
				}
			});
		};

		if ($this->loadIslandsOnMain()) {
			$island = $this->getIslandBy($worldName);
			if ($island !== null && $this->isWorldLoadedHere($worldName)) {
				if (!$island->isBlocked($player) || $player->isStaff()) {
					if ($player->isStaff() || $island->isPublic() || $island->getPermissions()->getPermissionsBy($player) !== null) {
						$island->teleportTo($player);
					} else {
						$player->sendMessage(TextFormat::RI . "You no longer have permissions on this island!");
					}
				} else {
					$player->sendMessage(TextFormat::RI . "You are blocked from visiting this island!");
				}
				return;
			}
			$loadFunction();
			return;
		}

		if ($elsewhere) {
			$server = $this->findIslandWorldServer($worldName);
			if (!$server instanceof SubServer) {
				if ($findServer) {
					$server = $this->findLeastPopulatedIslandServer();
					if (!$server instanceof SubServer) {
						$player->sendMessage(TextFormat::RI . "No server available to load island at... Please try again soon!");
						return;
					}
				} else {
					$player->gotoSpawn(TextFormat::RI . "This island is no longer available");
					return;
				}
			}
			if ($server->getIdentifier() === Core::thisServer()->getIdentifier()) {
				$loadFunction();
			} else {
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => $server->getIdentifier(),
					"action" => "island",
					"actionData" => ["world" => $worldName]
				]))->queue();
				//$server->transfer($player);
				SkyBlock::getInstance()->onQuit($player, true);
				$player->getGameSession()->save(true, function ($session) use ($player, $server): void {
					if ($player->isConnected()) {
						$server->transfer($player);
						$server->sendSessionSavedPacket($player, 1);
					}
					$player->getGameSession()->getSessionManager()->removeSession($player);
				});
				$player->sendMessage(TextFormat::YELLOW . "Saving game session data...");
			}
		} else {
			$loadFunction();
		}
	}

	public function loadIsland(string $world, \Closure $onCompletion, \Closure $error, bool $loadWorld = true): void {
		if (($island = $this->getIslandBy($world)) instanceof Island) {
			if ($loadWorld && !$this->isWorldLoadedHere($world)) {
				if (!$this->islandWorldExists($world)) {
					$error(self::ERROR_WORLD_NOT_FOUND);
					return;
				}
				$this->unloadIsland($island, false, false);
				$this->loadIsland($world, $onCompletion, $error, $loadWorld);
				return;
			}
			$error(self::ERROR_ALREADY_LOADED);
			return;
		} elseif ($loadWorld && ($server = $this->findIslandWorldServer($world)) instanceof SubServer) {
			if ($server->getIdentifier() !== Core::thisServer()->getIdentifier()) {
				$error(self::ERROR_LOADED_ELSEWHERE);
				return;
			}
		}

		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("load_island_$world", [
			new MySqlQuery("main", "SELECT * FROM islands WHERE world=?", [$world]),
			new MySqlQuery("challenges", "SELECT challenges FROM island_challenges WHERE world=?", [$world]),
			new MySqlQuery("permissions", "SELECT * FROM island_permissions WHERE world=?", [$world]),
			new MySqlQuery("warps", "SELECT * FROM island_warps WHERE world=?", [$world]),
			new MySqlQuery("warp_pads", "SELECT * FROM island_warp_pads WHERE world=?", [$world]),
			new MySqlQuery("shops", "SELECT * FROM island_shops WHERE world=?", [$world]),
			new MySqlQuery("texts", "SELECT * FROM island_texts WHERE world=?", [$world]),
		]), function (MySqlRequest $request) use ($world, $loadWorld, $onCompletion, $error): void {
			$island = $request->getQuery()->getResult()->getRows();
			if (count($island) === 0) {
				$error(self::ERROR_NOT_IN_DATABASE);
				return;
			}
			if ($loadWorld && !$this->isWorldLoadedHere($world)) {
				if (!$this->islandWorldExists($world)) {
					$error(self::ERROR_WORLD_NOT_FOUND);
					return;
				}
				$this->loadIslandWorld($world);
			}

			$warps = $request->getQuery("warps")->getResult()->getRows();
			$warpPads = $request->getQuery("warp_pads")->getResult()->getRows();
			$challenges = ($request->getQuery("challenges")->getResult()->getRows()[0] ?? [])["challenges"] ?? null;

			$shops = $request->getQuery("shops")->getResult()->getRows();
			$texts = $request->getQuery("texts")->getResult()->getRows();

			$island = $island[0];
			$xuids = [];
			$permissions = $request->getQuery("permissions")->getResult()->getRows();
			foreach ($permissions as $permission) {
				$xuids[] = $permission["xuid"];
			}
			$blockList = $island["blockList"] ?? [];
			$blockList = explode(",", rtrim($blockList));
			foreach ($blockList as $key => $blocked) {
				if ($blocked !== " " && $blocked !== "") {
					$xuids[] = $blocked;
				} else {
					unset($blockList[$key]);
				}
			}
			Core::getInstance()->getUserPool()->useUsers($xuids, function (array $users) use ($onCompletion, $island, $challenges, $permissions, $blockList, $warps, $warpPads, $shops, $texts): void {
				$perms = [];
				foreach ($permissions as $permission) {
					$perms[$permission["xuid"]] = new PlayerPermissions($users[$permission["xuid"]], $permission["world"], $permission["created"], $permission["version"], json_decode($permission["permissions"], true));
				}
				$blocked = [];
				foreach ($blockList as $block) {
					$blocked[$block] = $users[$block];
				}
				$this->islands[$island["world"]] = $island = new Island(
					$island["world"],
					$island["islandType"],
					$island["created"],
					$island["iname"],
					$island["description"],
					$island["sizelevel"],
					$island["time"],
					(bool) $island["public"],

					$island["permissionVersion"],
					json_decode($island["defaultVisitorPermissions"], true),
					json_decode($island["defaultInvitePermissions"], true),
					$perms,
					$blocked,
					new Vector3($island["spawnx"], $island["spawny"], $island["spawnz"]),
					$warps,
					$warpPads,
					$shops,
					$texts,
					$island["gens"],
					$island["spawners"],
					$island["hoppers"],
					$challenges
				);
				$onCompletion($island);
			}, true);
		});
	}

	/**
	 * Used to check island info before loading physical island
	 * Never stores loaded islands
	 */
	public function loadIslands(array $worlds, \Closure $onCompletion): void {
		$batchId = self::newIslandBatchId();
		$this->batchLoad[$batchId] = new IslandBatch($batchId, count($worlds), $onCompletion);
		foreach ($worlds as $world) {
			$this->loadIsland($world, function (Island $island) use ($batchId): void {
				$batch = $this->getIslandBatch($batchId);
				if ($batch !== null) {
					if ($batch->addLoadedIsland($island)) {
						unset($this->batchLoad[$batchId]);
					}
				}
			}, function (int $errorId) use ($batchId, $world): void {
				$batch = $this->getIslandBatch($batchId);
				if ($batch !== null) {
					if ($batch->addLoadedIsland($this->getIslandBy($world))) {
						unset($this->batchLoad[$batchId]);
					}
				}
			}, false);
		}
	}

	public function unloadIsland(Island $island, bool $save = true, bool $unloadWorld = true, bool $saveAsync = true): void {
		if ($save) {
			$island->save($saveAsync);
		}
		unset($this->islands[$island->getWorldName()]);
		if ($unloadWorld) {
			$this->unloadIslandWorld($island->getWorldName(), $save);
		}
	}

	public function deleteIsland(Island $island): void {
		$island->delete();
		$this->unloadIsland($island, false);
		$this->deleteIslandWorld($island->getWorldName());
	}

	//// Island Worlds / Generation ////
	public function registerGenerators(): void {
		$providerManager = Server::getInstance()->getWorldManager()->getProviderManager();
		$provider = new WritableWorldProviderManagerEntry(\Closure::fromCallable([IslandWorldProvider::class, 'isValid']), fn (string $path) => new IslandWorldProvider($path), \Closure::fromCallable([IslandWorldProvider::class, 'generate']));
		$providerManager->addProvider($provider, "island");

		foreach (Structure::ISLANDS as $id => $island) {
			$this->generators[$id] = new GeneratorInfo($id, $name = $island["name"], $class = $island["class"], "");
			GeneratorManager::getInstance()->addGenerator($class, "island_" . $name, \Closure::fromCallable(function (string $preset) use ($class): ?InvalidGeneratorOptionsException {
				if ($preset === "") {
					return null;
				}
				try {
					$class::convertSeed($preset);
					return null;
				} catch (InvalidGeneratorOptionsException $e) {
					return $e;
				}
			}));
		}
	}

	public function generateIslandWorld(string $worldName, int $islandType, bool $keepLoaded = false): bool {
		if (Server::getInstance()->getWorldManager()->generateWorld(
			$worldName,
			WorldCreationOptions::create()->setGeneratorClass($this->getGeneratorInfo($islandType)->getClass())
		)) {
			if (!$keepLoaded) {
				$this->unloadIslandWorld($worldName);
			} else {
				Server::getInstance()->getWorldManager()->loadWorld($worldName);
				Server::getInstance()->getWorldManager()->getWorldByName($worldName)->setAutosave(true);
			}
			return true;
		}
		return false;
	}

	public function islandWorldExists(string $worldName): bool {
		$ts = Core::thisServer();
		$server = $ts->isSubServer() ? $ts->getParentServer()->getIdentifier() : $ts->getIdentifier();
		return is_file(self::ISLANDS_LOCATION . $server . "/" . $worldName . ".zstd");
	}

	public function loadIslandWorld(string $worldName, bool $load = true): bool {
		if ($this->findIslandWorldServer($worldName) instanceof SubServer) {
			return false;
		}
		/** @var SubServer $ts */
		$ts = Core::thisServer();
		/** @var SubServer $server */
		$server = $ts->isSubServer() ? $ts->getParentServer()->getIdentifier() : $ts->getIdentifier();
		file_put_contents(
			($dir = self::SERVER_LOCATION . ($ts->isSubServer() ? $ts->getSubId(true) : $ts->getTypeId()) . "/worlds/") . $worldName . ".zstd",
			file_get_contents(self::ISLANDS_LOCATION . $server . "/" . $worldName . ".zstd"),
		);

		$data = zstd_decompress(file_get_contents($dir . $worldName . ".zstd"));
		file_put_contents($dir . $worldName . ".tar", $data);
		if (file_exists($dir . $worldName . ".zstd")) unlink($dir . $worldName . ".zstd");
		$tar = new \PharData($dir . $worldName . ".tar");
		@mkdir($dir . $worldName, 0777);
		$tar->extractTo($dir . $worldName);
		unset($tar);
		\Phar::unlinkArchive($dir . $worldName . ".tar");

		if ($load) {
			Server::getInstance()->getWorldManager()->loadWorld($worldName);
			Server::getInstance()->getWorldManager()->getWorldByName($worldName)->setAutosave(true);
		}
		return true;
	}

	public function saveIslandWorld(string $worldName): void {
		$world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
		if ($world instanceof World) {
			$world->save();
			Server::getInstance()->getWorldManager()->unloadWorld($world);
		}

		/** @var SubServer $ts */
		$ts = Core::thisServer();
		$server = $ts->isSubServer() ? $ts->getParentServer()->getIdentifier() : $ts->getIdentifier();
		if (is_file(self::ISLANDS_LOCATION .$server . "/" . $worldName . ".zstd")) {
			$this->deleteIslandWorld($worldName);
		}

		$location = self::SERVER_LOCATION . ($ts->isSubServer() ? $ts->getSubId(true) : $ts->getTypeId()) . "/worlds/" . $worldName;

		if (file_exists($worldName . ".tar")) unlink($worldName . ".tar");
		$phar = new \PharData(
			$worldName . ".tar",
			\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS,
			null,
			2
		);
		$data = $phar->buildFromDirectory($location);
		$compressed = zstd_compress(file_get_contents($worldName . ".tar"));
		unset($phar);
		\Phar::unlinkArchive($worldName . ".tar");
		file_put_contents(self::ISLANDS_LOCATION . $server . "/" . $worldName . ".zstd", $compressed);
	}

	public function unloadIslandWorld(string $worldName, bool $save = true): void {
		if ($save) {
			$this->saveIslandWorld($worldName);
		} else {
			$world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
			if ($world instanceof World) {
				Server::getInstance()->getWorldManager()->unloadWorld($world);
			}
		}

		/** @var SubServer $ts */
		$ts = Core::thisServer();
		Utils::recursiveDelete(self::SERVER_LOCATION . ($ts->isSubServer() ? $ts->getSubId(true) : $ts->getTypeId()) . "/worlds/" . $worldName);
	}

	public function deleteIslandWorld(string $worldName): void {
		$ts = Core::thisServer();
		$server = $ts->isSubServer() ? $ts->getParentServer()->getIdentifier() : $ts->getIdentifier();
		unlink(self::ISLANDS_LOCATION . $server . "/" . $worldName . ".zstd");
	}

	public function isWorldLoadedHere(string $worldName): bool {
		return ($is = $this->findIslandWorldServer($worldName)) !== null &&
			Core::thisServer()->getIdentifier() === $is->getIdentifier();
	}

	public function findIslandWorldServer(string $worldName): ?ServerInstance {
		if ($this->loadIslandsOnMain()) {
			/** @var SubServer */
			$server = Core::thisServer();
			$worlds = scandir($dir = self::SERVER_LOCATION . ($server->isSubServer() ? $server->getSubId(true) : $server->getTypeId()) . "/worlds");
			foreach ($worlds as $world) {
				if (self::isIslandWorld($world) && is_dir($dir . "/" . $world) && $worldName == $world) {
					return $server;
				}
			}
		}
		foreach (Core::thisServer()->getSubServers(true, $this->loadIslandsOnMain()) as $server) {
			if ($server->isSubServer() && $server->getSubId() === "pvp") continue;
			$worlds = scandir($dir = self::SERVER_LOCATION . ($server->isSubServer() ? $server->getSubId(true) : $server->getTypeId()) . "/worlds");
			foreach ($worlds as $world) {
				if (self::isIslandWorld($world) && is_dir($dir . "/" . $world) && $worldName == $world) {
					return $server;
				}
			}
		}
		return null;
	}

	public function findLeastPopulatedIslandServer(): ?SubServer {
		$serv = null;
		$wc = PHP_INT_MAX;
		foreach (Core::thisServer()->getSubServers(true, false) as $server) {
			if ($server->isSubServer() && $server->getSubId() === "pvp") continue;
			$total = 0;
			if ($server->isOnline()) {
				$worlds = scandir(($dir = self::SERVER_LOCATION . $server->getSubId(true) . "/worlds"));
				foreach ($worlds as $world) {
					if (self::isIslandWorld($world) && is_dir($dir . "/" . $world)) {
						$total++;
					}
				}
				if ($total < $wc) {
					$serv = $server;
					$wc = $total;
				}
			}
		}
		return $serv;
	}

	/**
	 * Returns all islands currently being used
	 */
	public function getAllOpenIslands(\Closure $onCompletion): void {
		$worlds = [];
		foreach (Core::thisServer()->getSubServers(true, ($this->loadIslandsOnMain())) as $server) {
			if ($server->isSubServer() && $server->getSubId() === "pvp") continue;
			if ($server->isOnline()) {
				$worldss = scandir(self::SERVER_LOCATION . ($server->isSubServer() ? $server->getSubId(true) : $server->getTypeId()) . "/worlds");
				foreach ($worldss as $world) {
					if (self::isIslandWorld($world)) {
						$worlds[$world] = $world;
					}
				}
			}
		}
		if (count($worlds) === 0) {
			$onCompletion($worlds);
			return;
		}
		$this->loadIslands($worlds, $onCompletion);
	}

	public function getAllPublicIslands(\Closure $onCompletion): void {
		$this->getAllOpenIslands(function (array $islands) use ($onCompletion): void {
			$public = [];
			foreach ($islands as $world => $island) {
				if ($island->isPublic()) $public[$world] = $island;
			}
			$onCompletion($public);
		});
	}

	public function getGeneratorInfo(int $id): ?GeneratorInfo {
		return $this->generators[$id] ?? null;
	}
}
