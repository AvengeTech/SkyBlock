<?php namespace skyblock;

use pocketmine\Server;
use pocketmine\entity\{
	Location,
	Skin
};
use pocketmine\inventory\{
	CallbackInventoryListener,
	Inventory,
    PlayerInventory
};
use pocketmine\item\{
	Item,
};
use pocketmine\player\{
	Player,
	GameMode
};
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;

use skyblock\commands\{
	ClearArmorStands,
	SpawnCommand,
	CratesCommand,
    EnderChest,
    PayCommand,
	StatsCommand,
	PlaytimeCommand,
	LeaderboardsCommand,
	Feed
};
use skyblock\{
	auctionhouse\AuctionHouse,
	combat\Combat,
	crates\Crates,
	data\Data,
	enchantments\Enchantments,
	fishing\Fishing,
	games\Games,
	generators\Generators,
	hud\Hud,
	islands\Islands,
	kits\Kits,
	koth\KOTH,
	lms\LMS,
	leaderboards\Leaderboards,
	parkour\Parkour,
	shop\Shops,
	spawners\Spawners,
	tags\Tags,
	techits\Techits,
	trade\Trade,
	trash\Trash
};
use skyblock\combat\CombatStat;
use skyblock\islands\{
	Island,
	IslandManager
};
use skyblock\islands\invite\Invite;
use skyblock\entity\{
	Earth,
	Clipboard,
	Controller,
	DollarSign,
	Mallet
};
use skyblock\leaderboards\ui\LeaderboardPrizesUi;
use skyblock\settings\SkyBlockSettings;
use skyblock\utils\stats\StatCycle;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\session\SessionManager;
use core\staff\anticheat\session\SessionManager as SessionSessionManager;
use core\user\User;
use core\utils\TextFormat;
use core\utils\entity\{
	AtIcon,
	Trophy
};

use pocketmine\block\tile\Hopper as TileHopper;
use skyblock\hoppers\Hoppers;
use skyblock\hoppers\tile\HopperTile;
use skyblock\pets\Pets;
use skyblock\spawners\tile\Spawner;

class SkyBlock extends PluginBase{

	const PLAYTIME_COOLDOWN = 30;

	public static ?self $instance = null;
	public static bool $test = false;

	public SessionManager $sessionManager;

	public AuctionHouse $auctionHouse;
	public Combat $combat;
	public Crates $crates;
	public Data $data;
	public Enchantments $enchantments;
	public Fishing $fishing;
	public Games $games;
	public Generators $generators;
	public Hud $hud;
	public Islands $islands;
	public Kits $kits;
	public KOTH $koth;
	public LMS $lms;
	public Leaderboards $leaderboards;
	public Parkour $parkour;
	public Pets $pets;
	public Shops $shops;
	public Spawners $spawners;
	public Tags $tags;
	public Techits $techits;
	public Trade $trade;
	public Trash $trash;

	public array $paycd = []; //Pay cooldown

	/** @var HopperTile[] */
	public array $hopperStore = [];
	/** @var Spawner[] */
	public array $spawnerStore = [];

	public array $cooldown = [];
	
	public static array $dataTransfers = [];

	public function onEnable() : void{
		self::$instance = $this;

		/** @var ServerInstance $ts */
		$ts = Core::getInstance()->getNetwork()->getServerManager()->getThisServer();
		$key = str_replace("-", "_", $ts->isSubServer() ? $ts->getParentServer()->getIdentifier() : $ts->getIdentifier());
		$this->sessionManager = new SessionManager($this, SkyBlockSession::class, $key);

		$this->getServer()->getWorldManager()->loadWorld("scifi1");

		$this->getServer()->getCommandMap()->registerAll("skyblock", [
			new ClearArmorStands($this, "cas", "Clear armor stands"),
			new SpawnCommand($this, "spawn", "Teleport back to spawn!"),
			new CratesCommand($this, "crates", "Teleport to the crates!"),
			new PayCommand($this, "pay", "Send techits to other players"),
			new StatsCommand($this, "stats", "View SkyBlock stats"),
			new PlaytimeCommand($this, "playtime", "View playtime"),
			new LeaderboardsCommand($this, "leaderboards", "Teleport to leaderboards"),
			new Feed($this, "feed", "Feed yoself! (Ranked)"),
			new EnderChest($this, 'enderchest', 'Open your Ender Chest remotely!')
		]);

		$this->getScheduler()->scheduleRepeatingTask(new MainTask($this), 1);
		$this->getServer()->getPluginManager()->registerEvents(new MainListener($this), $this);

		$this->auctionHouse = new AuctionHouse($this);
		$this->combat = new Combat($this);
		$this->data = new Data($this);
		$this->enchantments = new Enchantments($this);
		$this->games = new Games($this);
		$this->generators = new Generators($this);
		$this->crates = new Crates($this);
		$this->fishing = new Fishing($this);
		$this->hud = new Hud($this);
		$this->islands = new Islands($this);
		$this->kits = new Kits($this);
		$this->koth = new KOTH($this);
		$this->lms = new LMS($this);
		$this->leaderboards = new Leaderboards($this);
		$this->parkour = new Parkour($this);
		$this->pets = new Pets($this);
		$this->shops = new Shops($this);
		$this->spawners = new Spawners($this);
		new Hoppers;
		$this->tags = new Tags($this);
		$this->techits = new Techits($this);
		$this->trade = new Trade($this);
		$this->trash = new Trash($this);

		Core::getInstance()->getEntities()->getCustomEntityRegistry()->registerEntities([
			"game:crate",
			"game:supplydrop",
			"game:moneybag",

			"game:clipboard",
			"game:controller",
			"game:dollarsign",
			"game:mallet",

			"skyblock:island",
			"skyblock:earth",
		]);

		Core::getInstance()->getAnnounce()->setAfterAnnouncementClosure(function(Player $player) : void{
			/** @var SkyBlockPlayer $player */
			if($player->getGameSession()->getPlaytime()->isFirstJoin()){
				
			}
		});
		
		Core::getInstance()->getNetwork()->getServerManager()->addSubUpdateHandler(function(string $server, string $type, array $data) : void{
			switch($type){
				case "invite": //island invites
					$island = $data["island"];
					$from = $data["from"];
					$to = $data["to"];

					$status = $data["status"];
					switch($status){
						case Invite::STATUS_SENT:
							if(($pl = Server::getInstance()->getPlayerExact($to)) !== null){
								$pl->sendMessage(TextFormat::GI . "You received an island invite from " . TextFormat::YELLOW . $from . "! " . TextFormat::GRAY . "Type " . TextFormat::YELLOW . "/is invites" . TextFormat::GRAY . " to view it!");
							}
							Core::getInstance()->getUserPool()->useUsers([$to, $from], function(array $users) use($island, $to, $from) : void{
								$to = $users[$to];
								$from = $users[$from];
								$this->getIslands()->getIslandManager()->loadIsland($island, function(Island $island) use($from, $to) : void{
									$this->getIslands()->getInviteManager()->addInvite(new Invite(
										$island,
										$from,
										$to
									));
								}, function(int $error) use($island, $from, $to) : void{
									switch($error){
										case IslandManager::ERROR_ALREADY_LOADED:
											$this->getIslands()->getInviteManager()->addInvite(new Invite(
												$this->getIslands()->getIslandManager()->getIslandBy($island),
												$from,
												$to
											));
											break;
									}
								}, false);
							});
							break;
						case Invite::STATUS_ACCEPT:
							if(($pl = Server::getInstance()->getPlayerExact($from)) !== null){
								$pl->sendMessage(TextFormat::GI . $to . " accepted your island invite!");
							}
							$is = $this->getIslands()->getIslandManager()->getIslandBy($island);
							if($is !== null){
								$user = Core::getInstance()->getUserPool()->useUser($to, function(User $user) use($is) : void{
									$is->getPermissions()->addNewDefaultPermissions($user, true);
									if($is->isWorldLoaded()){
										$is->updateScoreboardLines();
									}
								});
							}
							$this->getIslands()->getInviteManager()->removeInvite($to, $island);
							break;
						case Invite::STATUS_DENY:
							if($this->getIslands()->getInviteManager()->hasInviteTo($to, $island)){
								if(($pl = Server::getInstance()->getPlayerExact($from)) !== null){
									$pl->sendMessage(TextFormat::RI . $to . " denied your island invite");
								}
								$this->getIslands()->getInviteManager()->removeInvite($to, $island);
							}
							break;
					}
					
					break;
				case "island": //sent when island permissions are updated
					$worldName = $data["world"];
					$im = SkyBlock::getInstance()->getIslands()->getIslandManager();
					if(($island = $im->getIslandBy($worldName)) !== null){
						$im->unloadIsland($island, false, false);
					}
					break;

				case "koth":
					$koth = $this->getKoth();
					$started = $data["started"] ?? false;
					$gameId = $data["gameId"] ?? 0;
					$typeId = $data["typeId"] ?? 0;
					$message = $data["message"] ?? "";
					if($started){
						$koth->getGameById($gameId)->setType($typeId)->setActive();
						if($message !== "") Server::getInstance()->broadcastMessage($message);
					}else{
						$game = $koth->getGameById($gameId);
						if($game !== null && $game->isActive()){
							$game->end();
							if($message !== "") Server::getInstance()->broadcastMessage($message);
						}
					}

				case "getkoth":
					$games = [];
					foreach($this->getKoth()->getActiveGames() as $game)
						$games[] = $game->getIdentifier() . ":" . $game->getType();

					(new ServerSubUpdatePacket([
						"server" => $server,
						"type" => "update",
						"data" => [
							"koth" => $games
						]
					]))->queue();
					break;

				case "update":
					if(!Core::thisServer()->isSubServer()){
						if(!self::pvpOnMain()){
							$koth = $data["koth"] ?? [];
							foreach($koth as $game){
								$data = explode(":", $game);
								$g = $data[0];
								$type = $data[1];
								$this->getKoth()->getGameById($g)?->setType($type)->setActive();
							}
						}else{
							$games = [];
							foreach($this->getKoth()->getActiveGames() as $game)
								$games[] = $game->getIdentifier() . ":" . $game->getType();

							(new ServerSubUpdatePacket([
								"server" => $server,
								"type" => "update",
								"data" => [
									"koth" => $games
								]
							]))->queue();
						}
					}else{
						if(self::pvpOnMain()){
							$games = [];
							foreach($this->getKoth()->getActiveGames() as $game)
								$games[] = $game->getIdentifier() . ":" . $game->getType();

							(new ServerSubUpdatePacket([
								"server" => $server,
								"type" => "update",
								"data" => [
									"koth" => $games
								]
							]))->queue();
						}else{
							$koth = $data["koth"] ?? [];
							foreach($koth as $game){
								$data = explode(":", $game);
								$g = $data[0];
								$type = $data[1];
								$this->getKoth()->getGameById($g)?->setType($type)->setActive();
							}
						}
					}
					break;


				case "lms":
					$lms = $this->getLms();
					$started = $data["started"] ?? false;
					$gameId = $data["gameId"] ?? 0;
					$message = $data["message"] ?? "";
					if($started){
						$lms->getGameById($gameId)->setActive();
						if($message !== "") Server::getInstance()->broadcastMessage($message);
					}else{
						$game = $lms->getGameById($gameId);
						if($game !== null && $game->isActive()){
							$game->end();
							if($message !== "") Server::getInstance()->broadcastMessage($message);
						}
					}
					break;

				case "getlms":
					$games = [];
					foreach($this->getLms()->getActiveGames() as $game)
						$games[] = $game->getId();

					(new ServerSubUpdatePacket([
						"server" => $server,
						"type" => "lmsupdate",
						"data" => [
							"lms" => $games
						]
					]))->queue();
					break;

				case "lmsupdate":
					if(!Core::thisServer()->isSubServer()){
						if(!self::pvpOnMain()){
							$lms = $data["lms"] ?? [];
							foreach($lms as $game){
								$this->getLms()->getGameById($game)?->setActive();
							}
						}else{
							$games = [];
							foreach($this->getLms()->getActiveGames() as $game)
								$games[] = $game->getId();

							(new ServerSubUpdatePacket([
								"server" => $server,
								"type" => "lmsupdate",
								"data" => [
									"lms" => $games
								]
							]))->queue();
						}
					}else{
						if(self::pvpOnMain()){
							$games = [];
							foreach($this->getLms()->getActiveGames() as $game)
								$games[] = $game->getId();

							(new ServerSubUpdatePacket([
								"server" => $server,
								"type" => "lmsupdate",
								"data" => [
									"lms" => $games
								]
							]))->queue();
						}else{
							$lms = $data["lms"] ?? [];
							foreach($lms as $game){
								$this->getLms()->getGameById($game)?->setActive();
							}
						}
					}
					break;

				//stat resets
				case "weekly":
					/** @var SkyBlockPlayer $player */
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						if($player->isLoaded()){
							$session = $player->getGameSession()->getCombat();
							$session->resetStats(CombatStat::TYPE_WEEKLY);
							$session->setChanged();

							$session = $player->getGameSession()->getKoth();
							$session->resetStats(CombatStat::TYPE_WEEKLY);
						}
					}
					break;
				case "monthly":
					/** @var SkyBlockPlayer $player */
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						if($player->isLoaded()){
							$session = $player->getGameSession()->getCombat();
							$session->resetStats(CombatStat::TYPE_MONTHLY);
							$session->setChanged();

							$session = $player->getGameSession()->getKoth();
							$session->resetStats(CombatStat::TYPE_MONTHLY);
						}
					}
					break;
				case "keyall":
					$type = $data["type"];
					$amount = $data["amount"];
					$colors = [
						"iron" => TextFormat::WHITE,
						"gold" => TextFormat::GOLD,
						"diamond" => TextFormat::AQUA,
						"emerald" => TextFormat::GREEN,
						"vote" => TextFormat::YELLOW,
						"divine" => TextFormat::RED,
					];
					/** @var SkyBlockPlayer $player */
					foreach($this->getServer()->getOnlinePlayers() as $player){
						if($player->isLoaded()){
							$player->sendMessage(TextFormat::GRAY . "Everyone online has received " . TextFormat::GREEN . "+" . $amount . " " . $colors[$type] . TextFormat::BOLD . strtoupper($type) . TextFormat::RESET . TextFormat::GREEN . " keys!");
							$session = $player->getGameSession()->getCrates();
							$session->addKeys($type, $amount);
						}
					}
					break;
			}
		});

		/** @var ServerInstance $ts */
		if(!($ts = Core::thisServer())->isSubServer()){
			if(!self::pvpOnMain()){
				(new ServerSubUpdatePacket([
					"server" => "skyblock-" . $ts->getTypeId() . "-pvp",
					"type" => "getkoth"
				]))->queue();
				(new ServerSubUpdatePacket([
					"server" => "skyblock-" . $ts->getTypeId() . "-pvp",
					"type" => "getlms"
				]))->queue();
			}
			$this->spawnIcons();
		}else{
			if(!self::pvpOnMain() && $ts->getSubId() === "pvp"){
				(new ServerSubUpdatePacket([
					"server" => "skyblock-" . $ts->getTypeId(),
					"type" => "getkoth"
				]))->queue();
				(new ServerSubUpdatePacket([
					"server" => "skyblock-" . $ts->getTypeId(),
					"type" => "getlms"
				]))->queue();
			}
		}

		//new StatCycle();
		Core::getInstance()->getVote()->setupPrizes("skyblock");
	}
	
	public static function pvpOnMain() : bool{
		return false;
		return !Core::thisServer()->isTestServer();
	}

	public function getSessionManager() : ?SessionManager{
		return $this->sessionManager;
	}

	public static function isLaggy() : bool{
		return Server::getInstance()->getTicksPerSecondAverage() < 18;
	}

	public function saveAll() : void{

	}

	public function onDisable() : void{
		$this->getAuctionHouse()->close();
		$this->getCombat()->removeLogs();
		$this->getGames()->close();
		$this->getIslands()->close();
		$this->getKoth()->close();
		$this->getLms()->close();
		$this->getTrash()->close();
		
		$this->getSessionManager()->close();
	}

	public function spawnIcons() : void{
		$pos = [
			[
				"x" => -14573.5,
				"y" => 152,
				"z" => 13664.5,
				"name" => TextFormat::AQUA . TextFormat::BOLD . "Go to spawn",
				"size" => 1.5,
				"yaw" => 0,
				"spin" => false,
				"func" => function(Player $player) : void{
					Server::getInstance()->dispatchCommand($player, "spawn");
				}
			],
			[
				"x" => -14567.5,
				"y" => 152,
				"z" => 13670.5,
				"name" => TextFormat::AQUA . TextFormat::BOLD . "Go to beginning",
				"size" => 1.5,
				"yaw" => 90,
				"spin" => false,
				"func" => function(Player $player) : void{
					Server::getInstance()->dispatchCommand($player, "parkour easy");
				}
			],

			[
				"x" => -14555.5,
				"y" => 163,
				"z" => 13533.5,
				"name" => TextFormat::AQUA . TextFormat::BOLD . "Go to spawn",
				"size" => 1.5,
				"yaw" => 0,
				"spin" => false,
				"func" => function(Player $player) : void{
					Server::getInstance()->dispatchCommand($player, "spawn");
				}
			],
			[
				"x" => -14561.5,
				"y" => 163,
				"z" => 13539.5,
				"name" => TextFormat::AQUA . TextFormat::BOLD . "Go to beginning",
				"size" => 1.5,
				"yaw" => 90,
				"spin" => false,
				"func" => function(Player $player) : void{
					Server::getInstance()->dispatchCommand($player, "parkour hard");
				}
			],

		];
		$world = $this->getServer()->getWorldManager()->getDefaultWorld();
		if($world !== null){ //double check
			foreach($pos as $key => $xyz){
				$chunk = $world->getChunk((int) $xyz["x"] >> 4, (int) $xyz["z"] >> 4);
				if($chunk === null){
					$world->loadChunk((int) $xyz["x"] >> 4, (int) $xyz["z"] >> 4);
				}

				$icon = new AtIcon(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, $xyz["yaw"] ?? 0, 0), new Skin("Standard_Custom", file_get_contents("/[REDACTED]/skins/techie.dat"), "", "geometry.humanoid.custom"), $xyz["name"] ?? "", $xyz["func"] ?? null, $xyz["size"] ?? 1, $xyz["spin"] ?? true);
				$icon->spawnToAll();
			}
		}

		/**$pos = [
			[
				"x" => -14693.5,
				"y" => 123,
				"z" => 13670.5,
				"func" => function(Player $player) : void{
					$player->showModal(new LeaderboardPrizesUi());
				}
			]
		];
		if($world !== null){ //double check
			foreach($pos as $key => $xyz){
				$chunk = $world->getChunk((int) $xyz["x"] >> 4, (int) $xyz["z"] >> 4);
				if($chunk === null){
					$world->loadChunk((int) $xyz["x"] >> 4, (int) $xyz["z"] >> 4);
				}

				$trophy = new Trophy(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, 140, 0), null, $xyz["func"] ?? null);
				$trophy->spawnToAll();
			}
		}*/

		$pos = [
			[
				"x" => -14612.5,
				"y" => 115.8,
				"z" => 13583.5,
			]
		];
		if($world !== null){ //double check
			$xyz = [
				"x" => -14612.5,
				"y" => 115.8,
				"z" => 13583.5,
			];
			$chunk = $world->getChunk((int) $xyz["x"] >> 4, (int) $xyz["z"] >> 4);
			if($chunk === null){
				$world->loadChunk((int) $xyz["x"] >> 4, (int) $xyz["z"] >> 4);
			}

			$earth = new Earth(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, 0, 0), null, 3);
			$earth->spawnToAll();
			
			$xyz = [
				"x" => -14610.5,
				"y" => 116.2,
				"z" => 13578,
			];
			$mallet = new Mallet(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, 0, 0), null);
			$mallet->spawnToAll();

			$xyz = [
				"x" => -14610.5,
				"y" => 116.2,
				"z" => 13589,
			];
			$clipboard = new Clipboard(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, -90, 0), null);
			$clipboard->spawnToAll();

			$xyz = [
				"x" => -14607,
				"y" => 116.2,
				"z" => 13581.5,
			];
			$controller = new Controller(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, -90, 0), null);
			$controller->spawnToAll();

			$xyz = [
				"x" => -14607,
				"y" => 116.2,
				"z" => 13585.5,
			];
			$dollarsign = new DollarSign(new Location($xyz["x"], $xyz["y"], $xyz["z"], $world, -90, 0), null);
			$dollarsign->spawnToAll();
		}
	}

	public static function getInstance() : self{
		return self::$instance;
	}

	public static function isTestServer() : bool{
		return self::$test;
	}

	public function getSpawnPosition() : Position{
		return new Position(-14583.5, 121, 13583.5, $this->getServer()->getWorldManager()->getWorldByName("scifi1"));
	}
	
	public function getAuctionHouse() : AuctionHouse{
		return $this->auctionHouse;
	}

	public function getCombat() : Combat{
		return $this->combat;
	}

	public function getCrates() : Crates{
		return $this->crates;
	}

	public function getData() : Data{
		return $this->data;
	}

	public function getEnchantments() : Enchantments{
		return $this->enchantments;
	}

	public function getFishing() : Fishing{
		return $this->fishing;
	}
	
	public function getGames() : Games{
		return $this->games;
	}

	public function getGenerators() : Generators{
		return $this->generators;
	}

	public function getHud() : Hud{
		return $this->hud;
	}

	public function getIslands() : Islands{
		return $this->islands;
	}

	public function getKits() : Kits{
		return $this->kits;
	}

	public function getKoth() : KOTH{
		return $this->koth;
	}

	public function getLms() : LMS{
		return $this->lms;
	}

	public function getLeaderboards() : Leaderboards{
		return $this->leaderboards;
	}

	public function getParkour() : Parkour{
		return $this->parkour;
	}

	public function getPets() : Pets{
		return $this->pets;
	}

	public function getShops() : Shops{
		return $this->shops;
	}

	public function getSpawners() : Spawners{
		return $this->spawners;
	}

	public function getTags() : Tags{
		return $this->tags;
	}

	public function getTechits() : Techits{
		return $this->techits;
	}

	public function getTrade() : Trade{
		return $this->trade;
	}

	public function getTrash() : Trash{
		return $this->trash;
	}

	public function hasPlaytimeCooldown(Player $player) : bool{
		return $this->getPlaytimeCooldown($player) > 0;
	}

	public function getPlaytimeCooldown(Player $player) : int{
		return ($this->cooldown[$player->getName()] ?? 0) - time();
	}

	public function setPlaytimeCooldown(Player $player) : void{
		$this->cooldown[$player->getName()] = time() + self::PLAYTIME_COOLDOWN;
	}
	
	public function onPreJoin(Player $player) : void{
		if(!Core::thisServer()->isSubServer()){
			$player->teleport($this->getSpawnPosition(), 90, 0);
		}
		$player->setGamemode(GameMode::ADVENTURE());
	}

	public function onJoin(Player $player) : void{
		/** @var SkyBlockPlayer $player */
		$player->setAllowFlight(true);
		
		$player->getGameSession()->getData()->give();
		if(
			($def = $player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::DEFAULT_ISLAND)) !== "" &&
			($ises = $player->getGameSession()->getIslands())->getPermissionsFor($def) !== null
		){
			$island = ($im = $this->getIslands()->getIslandManager())->getIslandBy($def);
			if($island === null){
				$im->loadIsland($def, function(Island $island) use($player) : void{
					if($player->isConnected()){
						$player->getGameSession()->getIslands()->setLastIslandAt($island);
					}
				}, function(int $error) use($player, $def, $im) : void{
					switch($error){
						case 0:
							if($player->isConnected()){
								$player->getGameSession()->getIslands()->setLastIslandAt($im->getIslandBy($def));
							}
							break;
					}
				}, false);
			}else{
				$ises->setLastIslandAt($island);
			}
		}
		$this->getLeaderboards()->onJoin($player);
		// $this->getEnchantments()->calculateCache($player);
		
		if(($game = $this->getGames()->getCurrentChatGame()) !== null){
			$game->send($player);
		}

		$player->getArmorInventory()->getListeners()->add(new CallbackInventoryListener(function(Inventory $inventory, int $slot, Item $oldItem) : void{
			/** @var PlayerInventory $inventory */
			// $this->getEnchantments()->calculateCache($inventory->getHolder());
		}, null));

		$this->getHud()->send($player);

		// Ignore this shane, I love u - Jay
		if ($player->getUser()->getXuid() == 2535417851980714) $player->getGameSession()->getTags()->addTag($this->tags->getTag("CaliKid"));
		if ($player->getUser()->getXuid() == 2535472340917821) $player->getGameSession()->getTags()->addTag($this->tags->getTag("Reformed>AT"));
		if ($player->getUser()->getXuid() == 2535420710653774) $player->getGameSession()->getTags()->addTag($this->tags->getTag("Ploogerrag"));
		if ($player->getUser()->getXuid() == 2535422608590078) $player->getGameSession()->getTags()->addTag($this->tags->getTag("crayon"));
	}

	public function onQuit(Player $player, bool $partial = false) : void{
		if(Core::getInstance()->getNetwork()->isShuttingDown()) return;
		/** @var SkyBlockPlayer $player */
		if(!$player->isLoaded()) return;

		$this->getGames()->onQuit($player);
		$this->getTrade()->onQuit($player);
		if($partial) return;
		
		if($this->getCombat()->getArenas()->inArena($player)){
			$this->getCombat()->getArenas()->getArena()->leaveArena($player);
		}
		$cs = $player->getGameSession()->getCombat();
		$mode = $cs->getCombatMode();
		if($mode->inCombat()){
			$mode->punish();
		}

		$player->getGameSession()->getIslands()->setIslandAt();
		$player->getGameSession()->getData()->update();

		$this->getLeaderboards()->onQuit($player);
	}

}
