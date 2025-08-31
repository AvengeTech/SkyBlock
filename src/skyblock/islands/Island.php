<?php namespace skyblock\islands;

use core\AtPlayer;
use pocketmine\Server;
use pocketmine\block\{
	Block,
	BlockLegacyIds,
	VanillaBlocks,
};
use pocketmine\block\tile\{
    Chest,
    Tile,
};
use pocketmine\item\{
	ItemFactory,
	ItemIds,
	VanillaItems
};
use pocketmine\entity\effect\{
	EffectInstance,
	VanillaEffects
};
use pocketmine\entity\{
	Entity,
	Location
};
use pocketmine\math\Vector3;
use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\{
	Position,
	World
};
use pocketmine\world\generator\{
	Generator,
};

use skyblock\SkyBlock;
use skyblock\islands\challenge\ChallengeManager;
use skyblock\islands\entity\IslandEntity;
use skyblock\islands\event\IslandUpgradeEvent;
use skyblock\islands\permission\{
	IslandPermissions,
	Permissions
};
use skyblock\islands\shop\{
	Shop,
	ShopManager
};
use skyblock\islands\text\{
	Text,
	TextManager
};
use skyblock\islands\warp\{
	Warp,
	WarpPad,
	WarpManager
};
use skyblock\settings\SkyBlockSettings;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\scoreboards\ScoreboardObject;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\staff\anticheat\session\SessionManager;
use core\user\User;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use skyblock\islands\world\generator\IslandGenerator;
use skyblock\SkyBlockPlayer;

class Island{

	//tutorial island world name
	const TUTORIAL = "2535420710653774-1-1690149568";

	const CHUNK_SPACE = 16;

	const GEN_CAP_BASE = 0;
	const SPAWNER_CAP_BASE = 2;
	const HOPPER_CAP_BASE = 5;

	const LIMIT_NAME = 32;
	const LIMIT_DESCRIPTION = 256;
	const LIMIT_BLOCK_LIST = 100;

	const LEVEL_EXTRA_CHUNKS = [
		1 => 0,
		2 => 1,
		5 => 2,
		7 => 3,
		8 => 4,
		10 => 5,
		13 => 6,
		15 => 7,
		20 => 10,
		25 => 12,
		30 => 15,
		50 => 30,
		75 => 50,
		100 => 100
	];

	const LEVEL_PRICES = [ // probably should automate this in the future
		2 => 15000,
		3 => 30000,
		4 => 60000,
		5 => 100000,
		6 => 150000,
		7 => 250000,
		8 => 500000,
		9 => 750000,
		10 => 1000000,
		11 => 1350000,
		12 => 1750000,
		13 => 2000000,
		14 => 3500000,
		15 => 5000000,
		16 => 7500000,
		17 => 10000000,
		18 => 15000000,
		19 => 25000000,
		20 => 50000000,
	];

	const EMPTY_UNLOAD = 60;
	const AUTOSAVE_TICKS = 300;

	public bool $saving = false;

	public int $saveTicks = 0;
	public int $emptyTicks = 0;

	public array $lines = [];
	public array $scoreboards = [];

	public ?IslandEntity $entity = null;

	public IslandPermissions $permissions;
	public WarpManager $warpManager;
	public ShopManager $shopManager;
	public TextManager $textManager;
	public ChallengeManager $challengeManager;

	public bool $deleted = false;

	public function __construct(
		public string $worldName,
		public int $islandType,
		public int $created,

		public string $name,
		public string $description,

		public int $sizeLevel,
		public int $time,

		public bool $public,

		string $permissionVersion,
		array $defaultVisitorPermissions,
		array $defaultInvitePermissions,
		array $permissions,

		public array $blockList,

		public Vector3 $spawnpoint,
		array $warps = [],
		array $warpPads = [],
		array $shops = [],
		array $texts = [],

		public int $gens = 0,
		public int $spawners = 0,
		public int $hoppers = 0,

		?string $challengeData = null
	){
		if($time !== -1 && $this->isWorldLoaded()){
			$this->getWorld()?->setTime($time);
			$this->getWorld()?->stopTime();
		}

		$this->permissions = new IslandPermissions($this, $permissionVersion, $defaultVisitorPermissions, $defaultInvitePermissions, $permissions);
		$this->warpManager = new WarpManager($this);
		foreach($warps as $warp){
			$this->getWarpManager()->addWarp(new Warp(
				$this->getWarpManager(),
				$warp["created"],
				$warp["name"], $warp["description"], $warp["hierarchy"],
				new Vector3($warp["locx"], $warp["locy"], $warp["locz"]),
				$warp["yaw"]
			));
		}
		foreach($warpPads as $warpPad){
			$this->getWarpManager()->addWarpPad(new WarpPad(
				$this->getWarpManager(),
				$warpPad["warp"],
				new Vector3($warpPad["posx"], $warpPad["posy"], $warpPad["posz"])
			));
		}

		$this->shopManager = new ShopManager($this);
		foreach($shops as $shop){
			$newShop = new Shop(
				$this->getShopManager(),
				$shop["created"],
				$shop["name"], $shop["description"], $shop["hierarchy"],
				new Vector3($shop["posx"], $shop["posy"], $shop["posz"]),
				$shop["bank"]
			);
			$newShop->parseJsonShopItems($shop["shopitems"]);
			$this->getShopManager()->addShop($newShop);
		}

		$this->textManager = new TextManager($this);
		foreach($texts as $text){
			$newText = new Text(
				$this->getTextManager(),
				$text["created"],
				$text["textdata"],
				new Vector3($text["posx"], $text["posy"], $text["posz"])
			);
			$this->getTextManager()->addText($newText);
		}

		$this->challengeManager = new ChallengeManager($this, $challengeData);

		$cn = ($tc = ($cm = $this->getChallengeManager())->getTotalChallengesCompleted()) >= ($cn2 = $cm->getChallengesNeededToLevelUp());
		$this->lines = [
			1 => (strlen($name) > 16 ? substr($name, 0, 16) . "..." : $name),
			2 => TextFormat::GRAY . "(" . TextFormat::YELLOW . $this->getPermissions()->getOwner()->getUser()->getGamertag() . TextFormat::GRAY . ")",
			3 => TextFormat::GRAY . "Uptime: ",
			4 => " ",
			5 => TextFormat::GRAY . "Level: " . TextFormat::YELLOW . $this->getSizeLevel() . TextFormat::GRAY . " (" . TextFormat::AQUA . $this->getDimensions() . TextFormat::GRAY . ")",
			6 => TextFormat::GRAY . "To level up:",
			7 => TextFormat::ICON_TOKEN . " " . TextFormat::AQUA . number_format($this->getLevelUpPrice()) . " techits",
			8 => TextFormat::EMOJI_TROPHY . " " . TextFormat::YELLOW . ($cn ? TextFormat::GREEN : TextFormat::RED) . $tc . TextFormat::GRAY . "/" . TextFormat::GREEN . $cn2 . TextFormat::GRAY . " challenges",
			9 => "  ",
			10 => TextFormat::GRAY . "Members: " . TextFormat::YELLOW . count($this->getPermissions()->getMembersOnIsland()) . TextFormat::GRAY . "/" . TextFormat::YELLOW . count($this->getPermissions()->getPermissions()),
			11 => TextFormat::GRAY . "Visitors: " . TextFormat::AQUA . $this->getVisitorCount(),
			12 => "   ",
			13 => TextFormat::AQUA . "store.avengetech.net",
		];
		$this->updateScoreboardLines(true, true, true, true, true);
	}

	public function tick() : bool{
		if($this->isWorldLoaded()){
			$this->saveTicks++;
			if($this->saveTicks >= self::AUTOSAVE_TICKS){
				$this->save();
				$this->saveTicks = 0;
			}
			$world = $this->getWorld();
			if(count($world->getPlayers()) > 0){
				$this->emptyTicks = 0;
				$this->updateScoreboardLines();
			}else{
				$this->emptyTicks++;
				if($this->emptyTicks >= self::EMPTY_UNLOAD){
					return false;
				}
			}
		}
		return true;
	}

	public function isDeleted() : bool{
		return $this->deleted;
	}

	public function delete() : void{
		$this->deleted = true;
		$this->getChallengeManager()->delete();
		$this->getPermissions()->delete();
		$this->getWarpManager()->delete();
		$this->getShopManager()->delete();
		$this->getTextManager()->delete();
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_island_" . $this->getWorldName(), new MySqlQuery("main",
				"DELETE FROM islands WHERE world=?", [$this->getWorldName()]
			)),
			function(MySqlRequest $request){}
		);
		foreach($this->getPlayers() as $player){
			$player->gotoSpawn(TextFormat::YI . "The island you were on has been deleted.");
		}
		$this->unloadElsewhere();
	}

	public function unloadElsewhere(bool $save = true, bool $saveAsync = true) : void{
		if($save) $this->save($saveAsync);

		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "island",
			"data" => [
				"world" => $this->getWorldName()
			]
		]))->queue();
	}

	public function isSaving() : bool{
		return $this->saving;
	}

	public function save(bool $async = true) : void{
		if($this->isSaving()) return;
		//echo "saving island", PHP_EOL;
		if($async){
			$this->saving = true;
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_" . $this->getWorldName(), new MySqlQuery("main",
					"INSERT INTO islands(
						world, islandType, created,
						iname, description,
						sizelevel,
						time,
						public,
						permissionVersion,
						defaultVisitorPermissions,
						defaultInvitePermissions,
						blockList,
						spawnx, spawny, spawnz,
						gens, spawners, hoppers
					) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						iname=VALUES(iname),
						description=VALUES(description),
						sizelevel=VALUES(sizelevel),
						time=VALUES(time),
						public=VALUES(public),
						permissionVersion=VALUES(permissionVersion),
						defaultVisitorPermissions=VALUES(defaultVisitorPermissions),
						defaultInvitePermissions=VALUES(defaultInvitePermissions),
						blockList=VALUES(blockList),
						spawnx=VALUES(spawnx), spawny=VALUES(spawny), spawnz=VALUES(spawnz),
						gens=VALUES(gens), spawners=VALUES(spawners), hoppers=VALUES(hoppers)",
					[
						$this->getWorldName(), $this->getIslandType(), $this->getCreated(),
						$this->getName(), $this->getDescription(),
						$this->getSizeLevel(),
						$this->getTime(),
						(int) $this->isPublic(),
						$this->getPermissions()->getVersion()->toString(),
						json_encode($this->getPermissions()->getDefaultVisitorPermissions()->getPermissions()),
						json_encode($this->getPermissions()->getDefaultInvitePermissions()->getPermissions()),
						$this->getBlockListString(),
						$this->getSpawnpoint()->getX(),
						$this->getSpawnpoint()->getY(),
						$this->getSpawnpoint()->getZ(),
						$this->getGenCount(), $this->getSpawnerCount(), $this->getHopperCount()
					]
				)),
				function(MySqlRequest $request){
					$this->saving = false;
					//echo "save complete", PHP_EOL;
				}
			);
		}else{
			$world = $this->getWorldName();
			$islandType = $this->getIslandType();
			$created = $this->getCreated();
			$name = $this->getName();
			$description = $this->getDescription();
			$sizelevel = $this->getSizeLevel();
			$time = $this->getTime();
			$public = (int) $this->isPublic();
			$version = $this->getPermissions()->getVersion()->toString();
			$dvp = json_encode($this->getPermissions()->getDefaultVisitorPermissions()->getPermissions());
			$dip = json_encode($this->getPermissions()->getDefaultInvitePermissions()->getPermissions());
			$blockList = $this->getBlockListString();
			$sx = $this->getSpawnpoint()->getX();
			$sy = $this->getSpawnpoint()->getY();
			$sz = $this->getSpawnpoint()->getZ();
			$gens = $this->getGenCount();
			$spawners = $this->getSpawnerCount();
			$hoppers = $this->getHopperCount();

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare(
				"INSERT INTO islands(
					world, islandType, created,
					iname, description,
					sizelevel,
					time,
					public,
					permissionVersion,
					defaultVisitorPermissions,
					defaultInvitePermissions,
					blockList,
					spawnx, spawny, spawnz,
					gens, spawners, hoppers
				) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
					iname=VALUES(iname),
					description=VALUES(description),
					sizelevel=VALUES(sizelevel),
					time=VALUES(time),
					public=VALUES(public),
					permissionVersion=VALUES(permissionVersion),
					defaultVisitorPermissions=VALUES(defaultVisitorPermissions),
					defaultInvitePermissions=VALUES(defaultInvitePermissions),
					blockList=VALUES(blockList),
					spawnx=VALUES(spawnx), spawny=VALUES(spawny), spawnz=VALUES(spawnz),
					gens=VALUES(gens), spawners=VALUES(spawners), hoppers=VALUES(hoppers)"
			);
			$stmt->bind_param("siissiiissssdddiii", $world, $islandType, $created, $name, $description, $sizelevel, $time, $public, $version, $dvp, $dip, $blockList, $sx, $sy, $sz, $gens, $spawners, $hoppers);
			$stmt->execute();
			$stmt->close();
		}
		$this->getChallengeManager()->save($async);
		$this->getPermissions()->save($async);
		$this->getWarpManager()->save($async);
		$this->getShopManager()->save($async);
		$this->getTextManager()->save($async);
	}

	public function isWorldLoaded() : bool{
		return SkyBlock::getInstance()->getIslands()->getIslandManager()->isWorldLoadedHere($this->getWorldName()); //check if world is loaded on this server
	}

	public function getWorldName() : string{
		return $this->worldName;
	}

	public function getWorld() : ?World{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
	}

	public function getCreated() : int{
		return $this->created;
	}

	public function getCreatedFormatted() : string{
		return date("m/d/y", $this->getCreated());
	}

	public function getPlayers() : array{
		$players = [];
		$world = $this->getWorld();
		if($world !== null){
			foreach($world->getPlayers() as $player){
				$players[] = $player;
			}
		}
		return $players;
	}

	public function getIslandType() : int{
		return $this->islandType;
	}

	public function getIslandTypeName() : string{
		return Structure::ISLANDS[$this->getIslandType()]["name"];
	}

	public function getGenerator() : ?IslandGenerator{
		$class = SkyBlock::getInstance()->getIslands()->getIslandManager()->getGeneratorInfo($this->getIslandType())->getClass();
		return new $class(0, "");
	}

	public function getName() : string{
		return $this->name;
	}

	public function setName(string $name) : void{
		$this->name = $name;
		$this->updateScoreboardLines(false, false, false, false, true);
		$this->unloadElsewhere();
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function setDescription(string $description) : void{
		$this->description = $description;
		$this->unloadElsewhere();
	}

	public function getSizeLevel() : int{
		return $this->sizeLevel;
	}

	public function getSizeLevelExtraChunks() : int{
		$chunks = self::LEVEL_EXTRA_CHUNKS[$this->getSizeLevel()] ?? null;
		$level = $this->getSizeLevel() - 1;
		while($chunks === null){
			$chunks = self::LEVEL_EXTRA_CHUNKS[$level] ?? null;
			$level--;
		}
		return $chunks;
	}

	public function getDimensions() : string{
		$extra = $this->getSizeLevelExtraChunks();
		$number = (($extra * 2) + 1) * 16;
		return $number . "x" . $number;
	}

	public function levelUp(Player $player, bool $charge = true) : void{
		/** @var SkyBlockPlayer $player */
		if($charge) $player->takeTechits($this->getLevelUpPrice());

		$this->sizeLevel++;

		$ev = new IslandUpgradeEvent($this, $player, $this->sizeLevel);
		$ev->call();

		$max = min(ChallengeManager::TOTAL_LEVELS, $this->getSizeLevel());

		for($i = 1; $i <= $max; $i++){
			$this->getChallengeManager()->getLevelSession($i);
		}

		$this->updateScoreboardLines(false, false, true);
	}

	public function getLevelUpPrice() : int{
		if($this->getSizeLevel() >= 20){
			return self::LEVEL_PRICES[20] + (1500000 * ($this->getSizeLevel() - 19));
		}
		return self::LEVEL_PRICES[$this->getSizeLevel() + 1] ?? -1;
	}

	public function getNextSizeUpgrade() : int{
		foreach(self::LEVEL_EXTRA_CHUNKS as $level => $size){
			if($level > $this->getSizeLevel())
				return $level;
		}
		return -1; //none bigger
	}

	public function getTime() : int{
		return $this->time;
	}

	public function setTime(int $time) : void{
		$this->time = $time % World::TIME_FULL;
		if($this->isWorldLoaded()){
			if($time !== -1){
				$this->getWorld()?->setTime($time % World::TIME_FULL);
				$this->getWorld()?->stopTime();
			}else{
				$this->getWorld()?->startTime();
			}
		}
	}

	public function isPublic() : bool{
		return $this->public;
	}

	public function setPublic(bool $value = false) : void{
		$this->public = $value;

		if(!$value){
			foreach($this->getPlayers() as $player){ //todo: check island player is on
				if($this->getPermissions()->getPermissionsBy($player) === null){
					$this->kick($player, "This island is no longer public.");
				}
			}
		}

		$this->unloadElsewhere();
	}

	public function getPermissions() : IslandPermissions{
		return $this->permissions;
	}

	public function getVisitorCount() : int{
		if(!$this->isWorldLoaded()) return 0;
		$count = 0;
		foreach($this->getWorld()->getPlayers() as $pl){
			/** @var AtPlayer $pl */
			if(!$pl->isVanished() && !$this->getPermissions()->isMember($pl)) $count++;
		}
		if($this->getWorld() !== null){
			return max(0, $count - count($this->getPermissions()->getMembersOnIsland()));
		}
		return 0;
	}

	public function getBlockList() : array{
		return $this->blockList;
	}

	public function getBlockListString() : string{
		$str = "";
		foreach($this->getBlockList() as $xuid => $blocked){
			$str .= $xuid . ",";
		}
		return rtrim($str);
	}

	public function isBlocked(Player|User $player) : bool{
		return isset($this->getBlockList()[$player->getXuid()]);
	}

	public function block(User $user) : void{
		$this->blockList[$user->getXuid()] = $user;
		$this->unloadElsewhere();

		if(($pl = $user->getPlayer()) !== null){
			if($pl->getWorld()->getDisplayName() === $this->getWorldName()){
				$this->kick($pl, "Blocked from visiting this island");
			}
		}
	}

	public function unblock(User $user) : void{
		unset($this->blockList[$user->getXuid()]);
		$this->unloadElsewhere();
	}

	public function getDefaultSpawnpoint() : Vector3{
		return $this->getGenerator()->getWorldSpawn();
	}

	public function getSpawnpoint() : Vector3{
		return $this->spawnpoint;
	}

	public function setSpawnpoint(Vector3 $pos) : void{
		$this->spawnpoint = $pos;
	}

	public function getTeleportationArea() : Position{
		$sp = $this->getSpawnpoint();
		return new Position($sp->getX(), $sp->getY(), $sp->getZ(), $this->getWorld());
	}

	public function getWarpManager() : WarpManager{
		return $this->warpManager;
	}

	public function getShopManager() : ShopManager{
		return $this->shopManager;
	}

	public function getTextManager() : TextManager{
		return $this->textManager;
	}

	public function getGenCount() : int{
		return $this->gens;
	}

	public function setGenCount(int $amount) : void{
		$this->gens = max(0, $amount);
	}

	public function getMaxGenCount() : int{
		return self::GEN_CAP_BASE + min(70, 5 * min(20, $this->getSizeLevel()));
	}

	public function addGen() : bool{
		if($this->getGenCount() >= $this->getMaxGenCount()){
			return false;
		}
		$this->setGenCount($this->getGenCount() + 1);
		return true;
	}

	public function takeGen() : void{
		$this->setGenCount($this->getGenCount() - 1);
	}

	public function getSpawnerCount() : int{
		return $this->spawners;
	}

	public function setSpawnerCount(int $amount) : void{
		$this->spawners = max(0, $amount);
	}

	public function getMaxSpawnerCount() : int{
		return self::SPAWNER_CAP_BASE + (min(15, $this->getSizeLevel()));
	}

	public function addSpawner() : bool{
		if($this->getSpawnerCount() >= $this->getMaxSpawnerCount()){
			return false;
		}
		$this->setSpawnerCount($this->getSpawnerCount() + 1);
		return true;
	}

	public function takeSpawner() : void{
		$this->setSpawnerCount($this->getSpawnerCount() - 1);
	}

	public function getHopperCount() : int{
		return $this->hoppers;
	}

	public function setHopperCount(int $amount) : void{
		$this->hoppers = max(0, $amount);
	}

	public function getMaxHopperCount() : int{
		return self::HOPPER_CAP_BASE + (5 * min(17, $this->getSizeLevel()));
	}

	public function addHopper() : bool{
		if($this->getHopperCount() >= $this->getMaxHopperCount()){
			return false;
		}
		$this->setHopperCount($this->getHopperCount() + 1);
		return true;
	}

	public function takeHopper() : void{
		$this->setHopperCount($this->getHopperCount() - 1);
	}

	public function getChallengeManager() : ChallengeManager{
		return $this->challengeManager;
	}

	public function islandChat(Player $player, string $message) : void {
		/** @var SkyBlockPlayer $player */
		$rc = $player->getSession()->getRank();
		$format = TextFormat::AQUA . TextFormat::BOLD . "[" . TextFormat::RESET . TextFormat::AQUA . ($rc->hasSub() && $rc->hasNick() ? "*" . $rc->getNick() : $player->getName()) . TextFormat::BOLD . "] " . TextFormat::RESET . TextFormat::AQUA . $message;

		foreach($this->getWorld()->getPlayers() as $pl){
			$pl->sendMessage($format);
		}
	}

	/* Functional shit */
	public function inZone($pos) : bool{
		if($pos instanceof Entity) $pos = $pos->getPosition();
		if($pos instanceof Tile || $pos instanceof Block) $pos = $pos->getPosition();

		if($pos instanceof Position && $pos->getWorld() !== $this->getWorld()) return false;

		$extraChunks = $this->getSizeLevelExtraChunks();
		$chunkPos = $this->getWorld()->getChunk(0 >> 4, 0 >> 4);
		if($chunkPos == null){
			$this->getWorld()->loadChunk(0 >> 4, 0 >> 4);
			$chunkPos = $this->getWorld()->getChunk(0 >> 4, 0 >> 4);
			if($chunkPos === null) return false;
		}

		$x = $pos->getFloorX() >> 4;
		$z = $pos->getFloorZ() >> 4;
		if($x > $extraChunks || $x < -$extraChunks || $z > $extraChunks || $z < -$extraChunks){
			return false;
		}
		return true;
	}

	public function kick(Player $player, string $reason = "") : bool {
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getIslands();
		if(!$session->atIsland()) return false;
		if(!$this->atIsland($player)) return false;

		$session->setIslandAt();
		$player->teleport(SkyBlock::getInstance()->getSpawnPosition(), 0, 0);
		$player->gotoSpawn(TextFormat::RI . "You were kicked from the island." . ($reason !== "" ? " Reason: " . $reason : ""));

		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void{
			$this->updateScoreboardLines(false, true);
		}), 5);
		return true;
	}

	public function atIsland(Player $player) : bool {
		/** @var SkyBlockPlayer $player */
		return ($is = $player->getGameSession()->getIslands())->atIsland() &&
			$is->getIslandAtId() == $this->getWorldName();
	}

	public function teleportTo(Player $player) : void {
		/** @var SkyBlockPlayer $player */
		$world = $this->getWorld();
		if($world === null){
			$player->gotoSpawn("Island world not loaded");
			return;
		}

		$ps = $player->getGameSession()->getParkour();
		if($ps->hasCourseAttempt()){
			$ps->getCourseAttempt()->removeScoreboard();
			$ps->setCourseAttempt();
		}

		$ksession = $player->getGameSession()->getKoth();
		if($ksession->inGame()){
			$ksession->setGame();
		}

		if(($arena = SkyBlock::getInstance()->getCombat()->getArenas()->getArena())->inArena($player)){
			$arena->leaveArena($player);
		}

		$perms = $this->getPermissions()->getPermissionsBy($player) ?? $this->getPermissions()->getDefaultVisitorPermissions();
		if ($perms->getPermission(Permissions::EDIT_BLOCKS) || $perms->getPermission(Permissions::EDIT_ORE_FROM_ORE_GENS)) {
			$player->setGamemode(GameMode::SURVIVAL());
		} else {
			$player->setGamemode(GameMode::ADVENTURE());
		}

		$isession = $player->getGameSession()->getIslands();
		if(
			!$isession->atIsland() ||
			$isession->getIslandAt()->getWorldName() !== $this->getWorldName()
		){
			$player->getGameSession()->getIslands()->setIslandAt();
			$player->getGameSession()->getIslands()->setIslandAt($this);
			$player->setFlightMode(false);

			$this->addScoreboard($player);

			$perm = ($ip = $this->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
			if($perm->getPermission(Permissions::USE_FLY) && $player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::AUTO_ISLAND_FLIGHT)){
				$player->setFlightMode(true);
			}
		}
		$player->teleport($this->getTeleportationArea());

		SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($player) : void{
			if($player->isConnected() && $this->atIsland($player)) $this->addScoreboard($player);
		}), 10);

		$this->updateScoreboardLines(false, true);

		$player->getEffects()->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 20 * 30, 0, false));
	}

	public function getIslandEntity() : ?IslandEntity{
		return $this->entity;
	}

	public function setIslandEntity(?IslandEntity $entity) : void{
		$this->entity = $entity;
	}

	/* Scoreboard */
	public function updateScoreboardLines(bool $uptime = true, bool $players = false, bool $requirements = false, bool $owner = false, bool $name = false) : void{
		if($uptime){
			$network = Core::getInstance()->getNetwork();
			$seconds = $network->getUptime();
			$hours = floor($seconds / 3600);
			$minutes = floor(((int) ($seconds / 60)) % 60);
			$seconds = $seconds % 60;
			if(strlen((string) $hours) == 1) $hours = "0" . $hours;
			if(strlen((string) $minutes) == 1) $minutes = "0" . $minutes;
			if(strlen((string) $seconds) == 1) $seconds = "0" . $seconds;
			$left = $network->getRestartTime() - time();
			$this->lines[3] = TextFormat::GRAY . "Uptime: " . TextFormat::RED . $hours . TextFormat::GRAY . ":" . TextFormat::RED . $minutes . TextFormat::GRAY . ":" . TextFormat::RED . $seconds . " " . ($seconds %3 == 0 ? TextFormat::EMOJI_HOURGLASS_EMPTY : TextFormat::EMOJI_HOURGLASS_FULL) . " " . ($left <= 60 ? ($seconds %2 == 0 ? TextFormat::EMOJI_CAUTION : "") : "");
		}
		if($players){
			$this->lines[10] = TextFormat::GRAY . "Members: " . TextFormat::YELLOW . count($this->getPermissions()->getMembersOnIsland()) . TextFormat::GRAY . "/" . TextFormat::YELLOW . count($this->getPermissions()->getPermissions());
			$this->lines[11] = TextFormat::GRAY . "Visitors: " . TextFormat::AQUA . $this->getVisitorCount();
		}
		if($requirements){
			$cn = ($tc = ($cm = $this->getChallengeManager())->getTotalChallengesCompleted()) >= ($cn2 = $cm->getChallengesNeededToLevelUp());

			$this->lines[5] = TextFormat::GRAY . "Level: " . TextFormat::YELLOW . $this->getSizeLevel() . TextFormat::GRAY . " (" . TextFormat::AQUA . $this->getDimensions() . TextFormat::GRAY . ")";
			$this->lines[7] = TextFormat::GRAY . TextFormat::ICON_TOKEN . " " . TextFormat::AQUA . number_format($this->getLevelUpPrice()) . " techits";
			$this->lines[8] = TextFormat::GRAY . TextFormat::EMOJI_TROPHY . " " . TextFormat::YELLOW . ($cn ? TextFormat::GREEN : TextFormat::RED) . $tc . TextFormat::GRAY . "/" . TextFormat::GREEN . $cn2 . TextFormat::GRAY . " challenges";
		}
		if($owner){
			$this->lines[2] = TextFormat::GRAY . "(" . TextFormat::YELLOW . $this->getPermissions()->getOwner()->getUser()->getGamertag() . TextFormat::GRAY . ")";
		}
		if($name){
			$name = $this->getName();
			$this->lines[1] = strlen($name) > 16 ? substr($name, 0, 16) . "..." : $name;
		}

		ksort($this->lines);
		$this->updateAllScoreboards();
	}

	public function getLines() : array{
		return $this->lines;
	}

	public function getLinesFor(Player $player) : array{
		$lines = $this->getLines();

		return $lines;
	}

	public function getScoreboards() : array{
		return $this->scoreboards;
	}

	public function getScoreboard(Player $player) : ?ScoreboardObject{
		return $this->scoreboards[$player->getXuid()] ?? null;
	}

	public function addScoreboard(Player $player, bool $removeOld = true) : void{
		if($removeOld){
			Core::getInstance()->getScoreboards()->removeScoreboard($player, true);
		}

		$scoreboard = $this->scoreboards[$player->getXuid()] = new ScoreboardObject($player);
		$scoreboard->send($this->getLines());
	}

	public function removeScoreboard(Player $player, bool $addOld = true) : void{
		$scoreboard = $this->getScoreboard($player);
		if($scoreboard !== null){
			unset($this->scoreboards[$player->getXuid()]);
			$scoreboard->remove();
		}
		if($addOld){
			Core::getInstance()->getScoreboards()->addScoreboard($player);
		}
	}

	public function removeAllScoreboards() : void{
		foreach($this->scoreboards as $xuid => $sb){
			if(($pl = $sb->getPlayer()) instanceof Player){
				$sb->remove();
				Core::getInstance()->getScoreboards()->addScoreboard($pl);
			}
			unset($this->scoreboards[$xuid]);
		}
	}

	public function updateAllScoreboards() : void{
		foreach($this->scoreboards as $xuid => $sb){
			if($sb->getPlayer() instanceof Player) $sb->update($this->getLinesFor($sb->getPlayer()));
		}
	}

	/* First creation */
	public function setup(Player $player) : void{
		$chestVec = $this->getGenerator()->getChestPosition();
		$this->getWorld()->setBlock($chestVec, VanillaBlocks::CHEST());

		$items = [
			VanillaItems::STRING()->setCount(12),
			VanillaBlocks::SAND()->asItem()->setCount(3),
			VanillaBlocks::ICE()->asItem()->setCount(2),
			VanillaBlocks::SUGARCANE()->asItem(),
			VanillaBlocks::BROWN_MUSHROOM()->asItem(),
			VanillaBlocks::RED_MUSHROOM()->asItem(),
			VanillaBlocks::CACTUS()->asItem(),
			VanillaItems::LAVA_BUCKET(),
			VanillaItems::MELON_SEEDS(),
		];

		/** @var Chest $chest */
		$chest = $this->getWorld()->getTile($chestVec);
		foreach($items as $item){
			$chest->getInventory()->addItem($item);
		}

		$isVec = $this->getGenerator()->getIslandEntityPosition();
		$is = new IslandEntity(Location::fromObject($isVec, $this->getWorld()));
		$is->spawnToAll();
	}

}