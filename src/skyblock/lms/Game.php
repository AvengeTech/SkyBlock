<?php namespace skyblock\lms;

use pocketmine\Server;
use pocketmine\player\{
	Player,
	GameMode
};
use pocketmine\world\{
	World,
	Position
};

use pocketmine\item\VanillaItems;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\koth\event\{
	KothWinEvent
};

use core\Core;
use core\network\protocol\{
	PlayerLoadActionPacket,
	ServerSubUpdatePacket
};
use core\scoreboards\ScoreboardObject;
use core\staff\anticheat\session\SessionManager;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use pocketmine\world\format\Chunk;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\item\MaxBook;

class Game{

	const GAME_WAITING = 0;
	const GAME_START = 1;
	const GAME_END = 2;

	public array $lines = [];
	public array $scoreboards = [];
	
	public array $players = [];
	public array $spectators = [];

	public bool $active = false;
	public int $state = self::GAME_WAITING;

	public array $tpCd = [];

	public ?Player $winner = null;
	public array $winnerRewardTexts = [];

	public function __construct(
		public string $id,
		public string $name,

		public string $level,
		int $time,

		public array $corners,
		public array $spawnpoints,
	){
		$lvl = $this->getLevel();
		$lvl?->setTime($time);
		$lvl?->stopTime();

		$this->lines = [
			1 => TextFormat::EMOJI_CONTROLLER . TextFormat::AQUA . " LMS Event " . TextFormat::EMOJI_CONTROLLER,
			2 => TextFormat::GRAY . "Map: " . TextFormat::GREEN . $this->getName(),
			3 => " ",
			4 => TextFormat::GRAY . "Uptime: ",
			5 => "  ",
			6 => TextFormat::EMOJI_MONEY_BAG . TextFormat::DARK_RED . " Your stats:",
			7 => TextFormat::GRAY . " - " . TextFormat::EMOJI_X . TextFormat::RED . " Kills: ",
			8 => TextFormat::GRAY . " - " . TextFormat::EMOJI_SKULL . TextFormat::YELLOW . " Deaths: ",
			9 => TextFormat::GRAY . " - " . TextFormat::EMOJI_STAR . TextFormat::GREEN . " Wins: ",
			10 => "   ",
			11 => TextFormat::EMOJI_LIGHTNING . TextFormat::AQUA . " Players left: " . TextFormat::RED . "0",
			12 => TextFormat::EMOJI_LIGHTNING . TextFormat::GREEN . " Status: " . TextFormat::RED . "Waiting...",
		];
	}

	public function tick() : void{
		$this->updateScoreboardLines();
		if($this->isStarted() && count($this->getPlayers()) <= 1){
			$this->winner = array_values($this->getPlayers())[0];
			$this->end(true);
		}
	}

	public function reward(Player $player) : void{
		/** @var SkyBlockPlayer $player */
		$this->winner = $player;

		$this->winnerRewardTexts[] = "50,000 techits";
		$player->getGameSession()->getCrates()->addKeys("emerald", $cnt = (mt_rand(1, 3) !== 1 ? mt_rand(20, 30) : mt_rand(40, 50)));
		$this->winnerRewardTexts[] = $cnt . " emerald keys";

		if(mt_rand(0, 1) === 0){
			$count = mt_rand(1, 5);
			$player->getInventory()->addItem(ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount($count));
			$this->winnerRewardTexts[] = $count . " Enchanted Golden Apples";
		}
		if(mt_rand(1, 10) === 1){
			if(mt_rand(1, 3) !== 1){
				$rarityType = mt_rand(1, 5);

				$count = match($rarityType){
					ED::RARITY_COMMON => 5,
					ED::RARITY_UNCOMMON => 4,
					ED::RARITY_RARE => 3,
					ED::RARITY_LEGENDARY => 2,
					ED::RARITY_DIVINE => 1,
					default => 1
				};

				$book = ItemRegistry::MAX_BOOK();
				$book->setup(MaxBook::TYPE_MAX_RARITY, $rarityType, true);
				$book->setCount($count);
			}else{
				$book = ItemRegistry::MAX_BOOK();
				$book->setup(MaxBook::TYPE_MAX_RANDOM_RARITY, -1, true);
				$book->setCount(3);
			}

			if($player->getInventory()->canAddItem($book)){
				$book->init();
				$player->getInventory()->addItem($book);
				$this->winnerRewardTexts[] = $book->getCount() . " " . $book->getName();
			}
		}
		if(mt_rand(1, 20) === 1){
			$player->getGameSession()->getCrates()->addKeys("divine", 1);
			$this->winnerRewardTexts[] = "1 divine key";
		}

		$player->addTechits(50000);

		//$ev = new KothWinEvent($this, $player);
		//$ev->call();

		$session = $player->getGameSession()->getLms();
		$session->addWin();
		$session->setCooldown();

		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "lms",
			"data" => [
				"started" => false,
				"gameId" => $this->getId(),
				"message" => TextFormat::GI . TextFormat::YELLOW . $player->getName() . TextFormat::LIGHT_PURPLE . " won the " . TextFormat::AQUA . $this->getName() . TextFormat::LIGHT_PURPLE . " LMS event! " . TextFormat::BOLD . TextFormat::GREEN . "GG"
			]
		]))->queue();

		$this->end();
	}

	public function getId() : string{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getLevel() : ?World{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getLevelName());
	}

	public function getLevelName() : string{
		return $this->level;
	}

	public function getCorners() : array{
		return $this->corners;
	}

	public function getSpawnpoints() : array{
		return $this->spawnpoints;
	}

	public function getRandomSpawn() : Position{
		$spawn = $this->spawnpoints[mt_rand(0, count($this->spawnpoints) - 1)];
		return new Position($spawn->getX(), $spawn->getY(), $spawn->getZ(), $this->getLevel());
	}

	public function tpCooldown(Player $player) : bool{
		return isset($this->tpCd[$player->getName()]) && $this->tpCd[$player->getName()] > time();
	}

	public function teleportTo(Player $player, bool $all = true, bool $tp = true) : void{
		/** @var SkyBlockPlayer $player */
		if(SkyBlock::pvpOnMain()){
			if(Core::thisServer()->isSubServer()){
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => Core::thisServer()->getParentServer()->getIdentifier(),
					"action" => "lms",
					"actionData" => ["gameId" => $this->getId()]
				]))->queue();
				$player->gotoSpawn();
				return;
			}
		}else{
			if(!($ts = Core::thisServer())->isSubServer() || $ts->getSubId() !== "pvp"){
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => "skyblock-" . $ts->getTypeId() . "-pvp",
					"action" => "lms",
					"actionData" => ["gameId" => $this->getId()]
				]))->queue();
				$player->gotoPvPserver();
				return;
			}
		}

		if($all){
			$isession = $player->getGameSession()->getIslands();
			if($isession->atIsland()){
				$isession->setIslandAt();
			}
			$ps = $player->getGameSession()->getParkour();
			if($ps->hasCourseAttempt()){
				$ps->getCourseAttempt()->removeScoreboard();
				$ps->setCourseAttempt();
			}
			$player->setFlightMode(false);

			if(($c = SkyBlock::getInstance()->getCombat())->getArenas()->inArena($player)){
				$c->getArenas()->getArena()->leaveArena($player);
			}

			$ksession = $player->getGameSession()->getKoth();
			if($ksession->inGame()){
				$ksession->setGame();
			}
			
			if($player->getWorld() !== $this->getLevel()){
				$player->getGameSession()->getCombat()->setInvincible();
			}

			if($this->getState() === self::GAME_WAITING){
				$this->addPlayer($player);
			}else{
				$this->addSpectator($player);
			}
			$player->getGameSession()->getLms()->setGame($this);
			$this->addScoreboard($player);

			if($player->getGamemode() == GameMode::SURVIVAL()){
				$player->setGamemode(GameMode::ADVENTURE());
			}
			$player->sendMessage(TextFormat::GI . "Teleported to active LMS game. (" . TextFormat::AQUA . $this->getName() . TextFormat::GRAY . ")");
			if($tp){
				$player->teleport($this->getRandomSpawn());
			}
		}elseif($tp){
			$player->teleport($this->getRandomSpawn());
		}
		$this->tpCd[$player->getName()] = time() + 2;
	}

	public function getPlayers() : array{
		return $this->players;
	}

	public function hasPlayer(Player $player) : bool{
		return isset($this->players[$player->getName()]);
	}

	public function addPlayer(Player $player) : void{
		$this->players[$player->getName()] = $player;
	}

	public function removePlayer(Player $player) : void{
		unset($this->players[$player->getName()]);
	}

	public function getSpectators() : array{
		return $this->spectators;
	}

	public function hasSpectator(Player $player) : bool{
		return isset($this->spectators[$player->getName()]);
	}

	public function addSpectator(Player $player) : void{
		$this->spectators[$player->getName()] = $player;
		$player->despawnFromAll();
		$player->setFlightMode(true);
	}

	public function removeSpectator(Player $player) : void{
		unset($this->spectators[$player->getName()]);
	}

	public function isActive() : bool{
		return $this->active;
	}

	public function setActive(bool $bool = true) : void{
		$this->active = $bool;
		if($bool) $this->setState(self::GAME_WAITING);
	}

	public function getState() : int{
		return $this->state;
	}

	public function setState(int $state) : void{
		$this->state = $state;
	}

	public function getStateName() : string{
		return match($this->getState()){
			self::GAME_WAITING => "Waiting...",
			self::GAME_START => "Started!",
			self::GAME_END => "End"
		};
	}

	public function isStarted() : bool{
		return $this->getState() === self::GAME_START;
	}

	public function start() : void{
		$this->setState(self::GAME_START);
		foreach(array_merge($this->getPlayers(), $this->getSpectators()) as $player){
			if($player->isConnected()) $player->sendMessage(TextFormat::GI . "Event has started! The last man standing wins");
		}
		$this->updateScoreboardLines();
	}

	public function end(bool $sendPacket = false) : void{
		if($this->winner !== null) Core::announceToSS(TextFormat::GI . TextFormat::AQUA . $this->winner->getName() . TextFormat::GRAY . " won the Last Man Standing event!");
		foreach($this->getPlayers() as $player){
			if(!$player->isLoaded()) continue;
			$mode = $player->getGameSession()->getCombat()->getCombatMode();
			if($mode->inCombat()){
				$mode->reset(false);
			}
			$textList = implode(PHP_EOL . TextFormat::GRAY . "- " . TextFormat::AQUA, $this->winnerRewardTexts);
			$player->gotoSpawn(
				$this->winner !== $player ? "" :
				TextFormat::GI . "You won the LMS match!"// and earned the following rewards: " . PHP_EOL .
				//TextFormat::GRAY . "- " . TextFormat::AQUA . $textList
			);
			$player->getGameSession()?->getLms()->setGame();
			$player->setFlightMode(false);
			$player->setAllowFlight(true);
		}
		foreach($this->getSpectators() as $player){
			if(!$player->isLoaded()) continue;
			$mode = $player->getGameSession()->getCombat()->getCombatMode();
			if($mode->inCombat()){
				$mode->reset(false);
			}
			$player->gotoSpawn();
			$player->getGameSession()?->getLms()->setGame();
			$player->setFlightMode(false);
			$player->setAllowFlight(true);
		}
		$this->setActive(false);
		$this->setState(self::GAME_END);

		if($sendPacket){
			$servers = [];
			foreach(Core::thisServer()->getSubServers(false, true) as $server){
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "lms",
				"data" => [
					"started" => false,
					"gameId" => $this->getId(),
					"message" => TextFormat::GI . TextFormat::LIGHT_PURPLE . "LMS match " . TextFormat::YELLOW . $this->getName() . TextFormat::LIGHT_PURPLE . " has been force ended."
				]
			]))->queue();
		}
	}

	/* Scoreboard */
	public function updateScoreboardLines() : void{
		$network = Core::getInstance()->getNetwork();
		$seconds = $network->getUptime();
		$hours = floor($seconds / 3600);
		$minutes = floor(((int) ($seconds / 60)) % 60);
		$seconds = $seconds % 60;
		if(strlen((string) $hours) == 1) $hours = "0" . $hours;
		if(strlen((string) $minutes) == 1) $minutes = "0" . $minutes;
		if(strlen((string) $seconds) == 1) $seconds = "0" . $seconds;
		$left = $network->getRestartTime() - time();
		$this->lines[4] = TextFormat::GRAY . "Uptime: " . TextFormat::RED . $hours . TextFormat::GRAY . ":" . TextFormat::RED . $minutes . TextFormat::GRAY . ":" . TextFormat::RED . $seconds . " " . ($seconds %3 == 0 ? TextFormat::EMOJI_HOURGLASS_EMPTY : TextFormat::EMOJI_HOURGLASS_FULL) . " " . ($left <= 60 ? ($seconds %2 == 0 ? TextFormat::EMOJI_CAUTION : "") : "");

		$this->lines[11] = TextFormat::EMOJI_LIGHTNING . TextFormat::AQUA . " Players left: " . TextFormat::GREEN . count($this->getPlayers());

		ksort($this->lines);
		$this->updateAllScoreboards();
	}

	public function getLines() : array{
		return $this->lines;
	}

	public function getLinesFor(Player $player) : array{
		/** @var SkyBlockPlayer $player */
		$lines = $this->getLines();

		if(!$player->isLoaded()) return $lines;
		$session = $player->getGameSession()?->getLms();
		if($session === null) return $lines;
		$lines[7] = TextFormat::GRAY . " - " . TextFormat::EMOJI_X . TextFormat::RED . " Kills: " . TextFormat::WHITE . number_format($session->getKills());// | " . $session->getWeeklyKills() . "[W] | " . $session->getMonthlyKills() . "[M]";
		$lines[8] = TextFormat::GRAY . " - " . TextFormat::EMOJI_SKULL . TextFormat::YELLOW . " Deaths: " . TextFormat::WHITE . number_format($session->getDeaths());// | " . $session->getWeeklyDeaths() . "[W] | " . $session->getMonthlyDeaths() . "[M]";
		$lines[9] = TextFormat::GRAY . " - " . TextFormat::EMOJI_STAR . TextFormat::GREEN . " Wins: " . TextFormat::WHITE . number_format($session->getWins());// | " . $session->getWeeklyWins() . "[W] | " . $session->getMonthlyWins() . "[M]";

		if($this->isStarted()){
			if($this->hasPlayer($player)){
				$lines[12] = TextFormat::GREEN . "You're still in!";
			}elseif($this->hasSpectator($player)){
				$lines[12] = TextFormat::RED . "You're out!";
			}
		}else{
				$lines[12] = TextFormat::EMOJI_LIGHTNING . TextFormat::GREEN . " Status: " . TextFormat::RED . "Waiting...";
		}

		ksort($lines);
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

}