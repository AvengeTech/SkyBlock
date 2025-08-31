<?php

namespace skyblock\koth\pieces;

use core\Core;
use core\network\protocol\PlayerLoadActionPacket;
use core\network\protocol\ServerSubUpdatePacket;
use core\network\server\SubServer;
use core\scoreboards\ScoreboardObject;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use skyblock\koth\entity\CenterCrystal;
use skyblock\koth\event\KothWinEvent;
use skyblock\koth\pieces\claim\Claim;
use skyblock\koth\pieces\claim\FullClaim;
use skyblock\koth\pieces\claim\LimitedClaim;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class Game{

	const TYPE_UNKNOWN = -1;
	const TYPE_FULL = 0;
	const TYPE_LIMITED = 1;
	
	private ?Claim $claim = null;
	private ?CenterCrystal $crystal = null;
	private ?DyeColor $lastcolor = null;
	protected ?SkyBlockPlayer $winner = null;

	private bool $active = false;

	private int $type = self::TYPE_UNKNOWN;
	
	private array $tpCd = [];
	private array $lines = [];
	private array $scoreboards = [];
	/** @var Vector3[] $glass */
	private array $glass = [];

	public function __construct(
		private string $identifier,
		private string $name,
		private string $world,

		int $time,

		private array $corners,
		private array $spawnpoints,
		private Vector3 $center,
		private int $distance = 5,

		array $glass = []
	){
		$world = $this->getWorld();
		$world->setTime($time);
		$world->stopTime();

		foreach($glass as $glassCoord){
			$this->glass[] = new Vector3($glassCoord[0], $glassCoord[1], $glassCoord[2]);
		}

		$pos = $this->getCenter()->add(0.5, 0, 0.5);
		$this->crystal = new CenterCrystal(new Location($pos->x, $pos->y, $pos->z, $this->getWorld(), 0, 0), $this);

		$this->lines = [
			1 => TF::EMOJI_CONTROLLER . TF::AQUA . " KOTH Event " . TF::EMOJI_CONTROLLER,
			2 => TF::GRAY . "Map: " . TF::GREEN . $this->getName(),
			3 => TF::GRAY . "Type: ",
			4 => " ",
			5 => TF::GRAY . "Uptime: ",
			6 => "  ",
			7 => TF::EMOJI_MONEY_BAG . TF::DARK_RED . " Your stats:",
			8 => TF::GRAY . " - " . TF::EMOJI_X . TF::RED . " Kills: ",
			9 => TF::GRAY . " - " . TF::EMOJI_SKULL . TF::YELLOW . " Deaths: ",
			10 => TF::GRAY . " - " . TF::EMOJI_STAR . TF::GREEN . " Wins: ",
			11 => "   ",
			12 => TF::EMOJI_LIGHTNING . TF::AQUA . " Claiming: " . TF::RED . "No one",
		];
	}

	public function tick() : void{
		if(is_null($this->claim)) return;

		$this->claim->tick();
	}

	public function reward(Player $player) : void{
		/** @var SkyBlockPlayer $player */
		$this->winner = $player;

		$item = ItemRegistry::KOTH_POUCH()->init();

		$player->getInventory()->addItem($item);

		$ev = new KothWinEvent($this, $player);
		$ev->call();

		$session = $player->getGameSession()->getKoth();
		$session->addWin();
		$session->setCooldown();

		$this->setGlassColor(DyeColor::YELLOW());

		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "koth",
			"data" => [
				"started" => false,
				"gameId" => $this->getIdentifier(),
				"message" => TF::GI . TF::YELLOW . $player->getName() . TF::LIGHT_PURPLE . " won the " . TF::AQUA . $this->getName() . TF::LIGHT_PURPLE . " KOTH event! " . TF::BOLD . TF::GREEN . "GG"
			]
		]))->queue();

		$this->end();
	}

	public function getIdentifier() : string{ return $this->identifier; }

	public function getType() : int{ return $this->type; }

	public function getTypeName() : string{ return ($this->type == 0 ? "Full Capture" : ($this->type == 1 ? "Limited Capture" : "Unknown")); }

	public function setType(int $type) : self{
		$this->type = $type;
		$this->claim = ($this->type === self::TYPE_FULL ? new FullClaim($this) : new LimitedClaim($this));

		$this->lines[3] = TF::GRAY . "Type: " . TF::DARK_PURPLE . $this->getTypeName();

		ksort($this->lines);
		$this->updateAllScoreboards();

		return $this;
	}

	public function getName() : string{ return $this->name; }

	public function getWorld() : ?World{ return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName()); }

	public function getWorldName() : string{ return $this->world; }

	public function getCorners() : array{ return $this->corners; }

	public function getSpawnpoints() : array{ return $this->spawnpoints; }

	public function getCenter() : Vector3{ return $this->center; }

	public function getDistance() : int{ return $this->distance; }

	public function isActive() : bool{ return $this->active; }

	public function setActive(bool $bool = true) : void{ $this->active = $bool; }

	public function getGlassData() : array{ return $this->glass; }

	public function getLastGlassColor(): ?DyeColor { return $this->lastcolor; }

	public function setGlassColor(DyeColor $color) : void{
		if($color == $this->getLastGlassColor()) return;

		$this->lastcolor = $color;

		foreach($this->getGlassData() as $coord){
			if(
				!$this->getWorld()->isChunkGenerated($coord->x >> Chunk::COORD_BIT_SIZE, $coord->z >> Chunk::COORD_BIT_SIZE) || 
				!$this->getWorld()->isChunkLoaded($coord->x >> Chunk::COORD_BIT_SIZE, $coord->z >> Chunk::COORD_BIT_SIZE)
			) continue;
			$this->getWorld()->setBlock($coord, VanillaBlocks::STAINED_GLASS()->setColor($color), true);
		}
	}

	public function tpCooldown(Player $player) : bool{
		return isset($this->tpCd[$player->getName()]) && $this->tpCd[$player->getName()] > time();
	}

	public function getRandomSpawn() : Position{
		$spawn = $this->spawnpoints[mt_rand(0, count($this->spawnpoints) - 1)];

		return new Position($spawn->getX(), $spawn->getY(), $spawn->getZ(), $this->getWorld());
	}

	public function teleportTo(Player $player, bool $all = true, bool $tp = true) : void{
		/** @var SkyBlockPlayer $player */
		if(SkyBlock::pvpOnMain()){
			if(Core::thisServer()->isSubServer()){
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => Core::thisServer()->getParentServer()->getIdentifier(),
					"action" => "koth",
					"actionData" => ["gameId" => $this->getIdentifier()]
				]))->queue();
				$player->gotoSpawn();
				return;
			}
		}else{
			/** @var SubServer $ts */
			if(!($ts = Core::thisServer())->isSubServer() || $ts->getSubId() !== "pvp"){
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => "skyblock-" . $ts->getTypeId() . "-pvp",
					"action" => "koth",
					"actionData" => ["gameId" => $this->getIdentifier()]
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

			$lsession = $player->getGameSession()->getLms();
			if($lsession->inGame()){
				$lsession->setGame();
			}
			
			if($player->getWorld() !== $this->getWorld()){
				$player->getGameSession()->getCombat()->setInvincible();
			}

			$player->getGameSession()->getKoth()->setGame($this);
			$this->addScoreboard($player);

			if($player->getGamemode() == GameMode::SURVIVAL()){
				$player->setGamemode(GameMode::ADVENTURE());
			}
			$player->sendMessage(TF::GI . "Teleported to active KOTH game. (" . TF::AQUA . $this->getName() . TF::GRAY . ")");
			if($tp){
				$player->teleport($this->getRandomSpawn());
			}
		}elseif($tp){
			$player->teleport($this->getRandomSpawn());
		}

		$this->tpCd[$player->getName()] = time() + 2;
	}

	public function inCenter(Player $player) : bool{
		return $player->getWorld()->getFolderName() === $this->getWorld()->getFolderName() && $player->getPosition()->distance($this->getCenter()) <= $this->getDistance();
	}

	public function isInBorder(Player $player) : bool{
		$corners = $this->getCorners();
		$x = $player->getPosition()->getX();
		$z = $player->getPosition()->getZ();

		return $x >= $corners[0][0] && $x <= $corners[1][0] && $z >= $corners[0][1] && $z <= $corners[1][1];
	}

	public function nudge(Player $player) : void{
		$center = $this->getCenter();
		$dv = $center->subtract($player->getPosition()->x, $player->getPosition()->y, $player->getPosition()->z)->normalize();
		$player->knockback($dv->x, $dv->z, 0.2);
	}

	
	/** @return SkyBlockPlayer[] */
	public function getPlayers() : array{
		return $this->getWorld()->getPlayers();
	}

	public function getClaim() : ?Claim{ return $this->claim; }

	public function end(bool $sendPacket = false) : void{
		foreach($this->getPlayers() as $player){
			if(!$player->isLoaded()) continue;

			$mode = $player->getGameSession()->getCombat()->getCombatMode();

			if($mode->inCombat()){
				$mode->reset(false);
			}

			$player->gotoSpawn(
				$this->winner !== $player ? "" :
				TF::GI . "You won the KOTH match, you received a koth pouch!"
			);
			$player->getGameSession()?->getKoth()->setGame();
			$player->setAllowFlight(true);
		}

		$this->getClaim()?->reset();
		$this->setActive(false);

		if($sendPacket){
			$servers = [];
			foreach(Core::thisServer()->getSubServers(false, true) as $server){
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "koth",
				"data" => [
					"started" => false,
					"gameId" => $this->getIdentifier(),
					"message" => TF::GI . TF::LIGHT_PURPLE . "KOTH match " . TF::YELLOW . $this->getName() . TF::LIGHT_PURPLE . " has been force ended."
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

		$this->lines[5] = TF::GRAY . "Uptime: " . TF::RED . $hours . TF::GRAY . ":" . TF::RED . $minutes . TF::GRAY . ":" . TF::RED . $seconds . " " . ($seconds % 3 == 0 ? TF::EMOJI_HOURGLASS_EMPTY : TF::EMOJI_HOURGLASS_FULL) . " " . ($left <= 60 ? ($seconds %2 == 0 ? TF::EMOJI_CAUTION : "") : "");

		$this->lines[12] = TF::EMOJI_LIGHTNING . TF::AQUA . " Claiming: " . (($claimer = $this->getClaim()?->getClaimer()) !== null ? TF::GREEN . $claimer->getName() : TF::RED . "No one");
		if(($claim = $this->claim) instanceof LimitedClaim){
			$this->lines[13] = "    ";
			$this->lines[14] = TF::GRAY . "Time Left: " . TF::YELLOW . gmdate("i:s", $claim->getTimeEnd() - time());
		}

		ksort($this->lines);
		$this->updateAllScoreboards();
	}

	public function getLinesFor(Player $player) : array{
		/** @var SkyBlockPlayer $player */
		$lines = $this->getLines();

		if(!$player->isLoaded()) return $lines;
		$session = $player->getGameSession()?->getKoth();
		if($session === null) return $lines;
		$lines[8] = TF::GRAY . " - " . TF::EMOJI_X . TF::RED . " Kills: " . TF::WHITE . number_format($session->getKills());// | " . $session->getWeeklyKills() . "[W] | " . $session->getMonthlyKills() . "[M]";
		$lines[9] = TF::GRAY . " - " . TF::EMOJI_SKULL . TF::YELLOW . " Deaths: " . TF::WHITE . number_format($session->getDeaths());// | " . $session->getWeeklyDeaths() . "[W] | " . $session->getMonthlyDeaths() . "[M]";
		$lines[10] = TF::GRAY . " - " . TF::EMOJI_STAR . TF::GREEN . " Wins: " . TF::WHITE . number_format($session->getWins());// | " . $session->getWeeklyWins() . "[W] | " . $session->getMonthlyWins() . "[M]";

		ksort($lines);
		return $lines;
	}

	public function getLines() : array{ return $this->lines; }

	public function getScoreboards() : array{ return $this->scoreboards; }

	public function getScoreboard(Player $player) : ?ScoreboardObject{ return $this->scoreboards[$player->getXuid()] ?? null; }

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