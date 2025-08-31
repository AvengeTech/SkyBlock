<?php namespace skyblock\combat\arenas;

use pocketmine\Server;
use pocketmine\player\{
	Player,
	GameMode
};
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\{
	World,
	Position
};
use pocketmine\entity\effect\{
	EffectInstance,
	VanillaEffects
};
use pocketmine\math\Vector3;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use core\Core;
use core\network\protocol\PlayerLoadActionPacket;
use core\network\server\SubServer;
use core\scoreboards\ScoreboardObject;
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;

class Arena{

	public int $ticks = 0;

	public DropManager $dropManager;
	
	public array $players = [];

	public array $compass = [];

	public array $lines = [];
	public array $scoreboards = [];

	public array $teleporting = []; //for multi server
	
	public function __construct(
		public string $id,
		public bool $locked,

		public string $name,

		public string $worldName,

		public array $spawnpoints,
		public array $corners,
		public Vector3 $center,
		
		array $supplyDropPositions = [],
		array $moneyBagPositions = []
	){
		Server::getInstance()->getWorldManager()->loadWorld($worldName, true);
		$this->getWorld()->setTime(0);
		$this->getWorld()->stopTime();

		$this->dropManager = new DropManager($this, $supplyDropPositions, $moneyBagPositions);

		$this->setupCompass();

		$this->lines = [
			1 => TextFormat::EMOJI_SKULL . TextFormat::AQUA . " WARZONE " . TextFormat::EMOJI_SKULL,
			2 => TextFormat::GRAY . "Map: " . TextFormat::GREEN . $this->getName(),
			3 => " ",
			4 => TextFormat::GRAY . "Uptime: ",
			5 => "  ",
			6 => TextFormat::EMOJI_MONEY_BAG . TextFormat::DARK_RED . " Your stats:",
			7 => TextFormat::GRAY . " - " . TextFormat::EMOJI_X . TextFormat::RED . " Kills: ",
			8 => TextFormat::GRAY . " - " . TextFormat::EMOJI_SKULL . TextFormat::YELLOW . " Deaths: ",
			9 => TextFormat::GRAY . " - " . TextFormat::EMOJI_STAR . TextFormat::GREEN . " Wins: ",
			//10 => "   ",
			//11 => TextFormat::EMOJI_LIGHTNING . TextFormat::AQUA . " Compass:",
			//12 => "||||||||||||||||",
		];
		
		if(SkyBlock::pvpOnMain()){
			if(Core::thisServer()->isSubServer()) $this->ticks = -1;
		}else{
			/** @var SubServer $ts */
			if(!($ts = Core::thisServer())->isSubServer() || $ts->getSubId() !== "pvp")
				$this->ticks = -1;
		}
	}

	public function tick() : void{
		if($this->ticks === -1) return;

		$this->ticks++;

		if($this->ticks %4 == 0){
			$this->getDropManager()->tick();
			$this->updateScoreboardLines();
		}

		$this->updateAllScoreboards();
	}
	
	public function getDropManager() : DropManager{
		return $this->dropManager;
	}

	public function setupCompass() : void{
		$compass = array_fill(0, 359, TextFormat::RESET . TextFormat::GRAY . "|");
		$compass[0] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "S";
		$compass[89] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "W";
		$compass[179] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "N";
		$compass[269] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "E";
		$compass[44] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "S";
		$compass[45] = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "W";
		$compass[134] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "N";
		$compass[135] = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "W";
		$compass[224] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "N";
		$compass[225] = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "E";
		$compass[314] = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "S";
		$compass[315] = TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "E";

		$compass = array_merge($compass, $compass, $compass);
		$this->compass = $compass;
	}

	public static function wrapCompass(float $angle) : float{
		return ($angle + ceil(-$angle / 360) * 360);
	}

	public function getCompass(Player $player, float $direction, int $width = 20) : string{
		$compass = $this->compass;
		foreach($this->getDropManager()->getSpawnedSupplyDrops() as $drop){
			$horizontal = sqrt(($drop->getPosition()->x - $player->getPosition()->x) ** 2 + ($drop->getPosition()->z - $player->getPosition()->z) ** 2);
			$vertical = $drop->getPosition()->y - ($player->getPosition()->y + $player->getEyeHeight());
			$xDist = $drop->getPosition()->x - $player->getPosition()->x;
			$zDist = $drop->getPosition()->z - $player->getPosition()->z;
			$yaw = (int) (atan2($zDist, $xDist) / M_PI * 180 - 90);
			if($yaw < 0){
				$yaw += 360;
			}
			$compass[$yaw] = TextFormat::EMOJI_STAR;
			$compass[$yaw + 360] = TextFormat::EMOJI_STAR;
		}

		foreach($this->getDropManager()->getSpawnedMoneyBags() as $drop){
			$horizontal = sqrt(($drop->getPosition()->x - $player->getPosition()->x) ** 2 + ($drop->getPosition()->z - $player->getPosition()->z) ** 2);
			$vertical = $drop->getPosition()->y - ($player->getPosition()->y + $player->getEyeHeight());
			$xDist = $drop->getPosition()->x - $player->getPosition()->x;
			$zDist = $drop->getPosition()->z - $player->getPosition()->z;
			$yaw = (int) (atan2($zDist, $xDist) / M_PI * 180 - 90);
			if($yaw < 0){
				$yaw += 360;
			}
			$compass[$yaw] = TextFormat::EMOJI_MONEY_BAG;
			$compass[$yaw + 360] = TextFormat::EMOJI_MONEY_BAG;
		}

		$direction = self::wrapCompass($direction);

		return implode(array_slice($compass, (int) ($direction + 360 - $width), $width * 2 + 2));
	}

	public function getId() : string{
		return $this->id;
	}

	public function isLocked() : bool{
		return $this->locked;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getWorld() : ?World{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
	}

	public function getWorldName() : string{
		return $this->worldName;
	}

	public function getSpawnpoints() : array{
		return $this->spawnpoints;
	}

	public function getCorners() : array{
		return $this->corners;
	}

	public function isInBorder(Player $player) : bool{
		$corners = $this->getCorners();
		$x = $player->getPosition()->getX();
		$z = $player->getPosition()->getZ();
		return $x >= $corners[0][0] && $x <= $corners[1][0] && $z >= $corners[0][1] && $z <= $corners[1][1];
	}

	public function goBack(Player $player) : void{
		$player->sendTip(TextFormat::RED . "Do NOT attempt to leave the arena!!");
		$this->teleportTo($player);
		$player->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * 15, 1));
	}

	public function getRandomSpawn() : Position{
		$spawn = $this->spawnpoints[mt_rand(0, count($this->spawnpoints) - 1)];
		return Position::fromObject($spawn, $this->getWorld());
	}

	public function teleportTo(Player $player, bool $all = true, bool $tp = true){
		/** @var SkyBlockPlayer $player */
		if(SkyBlock::pvpOnMain()){
			if(($ts = Core::thisServer())->isSubServer()){
				$ps = $ts->getParentServer();
				if($ps->isOnline()){
					$pk = new PlayerLoadActionPacket([
						"player" => $player->getName(),
						"server" => $ps->getIdentifier(),
						"action" => "arena",
						"actionData" => ["id" => $this->getId()]
					]);
					$pk->queue();

					$player->gotoSpawn();
				}
				return;
			}
		}else {
			/** @var SubServer $ts */
			if(
				(!isset($this->teleporting[$player->getName()]) || $this->teleporting[$player->getName()] <= time()) &&
				(!($ts = Core::thisServer())->isSubServer() || $ts->getSubId() !== "pvp")
			){
				$this->teleporting[$player->getName()] = time() + 3;
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => "skyblock-" . $ts->getTypeId() . "-pvp",
					"action" => "arena",
					"actionData" => ["id" => $this->getId()]
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
			if($player->getGamemode() === GameMode::SURVIVAL()){
				$player->setGamemode(GameMode::ADVENTURE());
			}

			$this->players[$player->getName()] = 0;


			$this->addScoreboard($player);
			if($tp){
				$food = $player->getHungerManager()->getFood();
				$sat = $player->getHungerManager()->getSaturation();
				$player->teleport($this->getRandomSpawn());
				Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($food, $sat, $player) : void{
					if($player->isConnected()){
						$player->getHungerManager()->setFood($food);
						$player->getHungerManager()->setSaturation($sat);
					}
				}), 2);
			}
		}elseif($tp){
			$food = $player->getHungerManager()->getFood();
			$sat = $player->getHungerManager()->getSaturation();
			$player->teleport($this->getRandomSpawn());
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($food, $sat, $player) : void{
				if($player->isConnected()){
					$player->getHungerManager()->setFood($food);
					$player->getHungerManager()->setSaturation($sat);
				}
			}), 2);
		}
	}

	public function getCenter() : Vector3{
		return $this->center;
	}
	
	public function inArena(Player $player) : bool{
		return isset($this->players[$player->getName()]);
	}
	
	public function leaveArena(Player $player) : void{
		$this->removeScoreboard($player);
		unset($this->players[$player->getName()]);
	}
	
	public function addStreak(Player $player) : void{
		if($this->inArena($player)){
			$streak = ++$this->players[$player->getName()];

			if($streak %5 == 0){
				Server::getInstance()->broadcastMessage(TextFormat::GI . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " is on a " . TextFormat::AQUA . $streak . " kill streak!");
			}
		}
	}

	public function getPlayers(bool $literal = false) : array{
		if(!$literal) return $this->players;
		
		return $this->getWorld()?->getPlayers() ?? []; //see if this causes problems
		
		$players = [];
		foreach($this->players as $name => $streak){
			$player = Server::getInstance()->getPlayerExact($name);
			if($player !== null) $players[] = $player;
		}
		return $players;
	}


	/** Compass */

	
	/** Scoreboards */
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

		ksort($this->lines);
		$this->updateAllScoreboards();
	}

	public function getLines() : array{
		return $this->lines;
	}

	public function getLinesFor(Player $player) : array{
		/** @var SkyBlockPlayer $player */
		$lines = $this->getLines();
		
		$session = $player->getGameSession()?->getCombat();
		if($session === null) return $lines;
		$lines[7] = TextFormat::GRAY . " - " . TextFormat::EMOJI_X . TextFormat::RED . " Kills: " . TextFormat::WHITE . number_format($kills = $session->getKills());// | " . $session->getWeeklyKills() . "[W] | " . $session->getMonthlyKills() . "[M]";
		$lines[8] = TextFormat::GRAY . " - " . TextFormat::EMOJI_SKULL . TextFormat::YELLOW . " Deaths: " . TextFormat::WHITE . number_format($deaths = $session->getDeaths());// | " . $session->getWeeklyDeaths() . "[W] | " . $session->getMonthlyDeaths() . "[M]";
		$lines[9] = TextFormat::GRAY . " - " . TextFormat::EMOJI_STAR . TextFormat::GREEN . " KDR: " . TextFormat::WHITE . ($deaths === 0 ? "N/A" : round($kills / $deaths, 2));// | " . $session->getWeeklyWins() . "[W] | " . $session->getMonthlyWins() . "[M]";

		//$lines[12] = $this->getCompass($player, $player->getLocation()->getYaw());

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