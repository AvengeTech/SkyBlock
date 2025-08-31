<?php namespace skyblock\leaderboards;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\leaderboards\command\Prizes;
use skyblock\leaderboards\types\{
	VoteStreakLeaderboard,

	TopIslandLevelLeaderboard,

	ParkourAllTimeCompletionsLeaderboard,
	ParkourBestTimeLeaderboard,

	MysqlUpdate,

	ArenaKillsLeaderboard,
	ArenaDropsLeaderboard,
	ArenaMobsLeaderboard,
	KothWinsLeaderboard,
	KothKillsLeaderboard,

	KeysLeaderboard,
	KeysOpenedLeaderboard,
	
	MultiLeaderboard,

	TechitsLeaderboard,
	FishingCatchesLeaderboard,
	Leaderboard
};

use core\Core;

class Leaderboards{

	const UPDATE_TICKS = 600;

	public int $ticks = 0;

	/** @var Leaderboard[] $leaderboards */
	public array $leaderboards = [];

	public array $left = [];

	public function __construct(public SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->registerAll("leaderboards", [
			new Prizes($plugin, "lbprizes", "View weekly/monthly leaderboard prizes!"),
		]);

		if(Core::thisServer()->isSubServer()) return;

		$this->leaderboards["arena_kills"] = new MultiLeaderboard([
			new ArenaKillsLeaderboard(0),
			new ArenaKillsLeaderboard(1),
			new ArenaKillsLeaderboard(2),
		], "arena_kills", 3, 5);

		$this->leaderboards["arena_drops"] = new MultiLeaderboard([
			new ArenaDropsLeaderboard(0),
			new ArenaDropsLeaderboard(1),
			new ArenaDropsLeaderboard(2),
		], "arena_drops", 3, 5);

		$this->leaderboards["arena_mobs"] = new MultiLeaderboard([
			new ArenaMobsLeaderboard(0),
			new ArenaMobsLeaderboard(1),
			new ArenaMobsLeaderboard(2),
		], "arena_mobs", 3, 5);

		$this->leaderboards["koth_kills"] = new MultiLeaderboard([
			new KothKillsLeaderboard(0),
			new KothKillsLeaderboard(1),
			new KothKillsLeaderboard(2)
		], "koth_kills", 3, 5);
		
		$this->leaderboards["koth_wins"] = new MultiLeaderboard([
			new KothWinsLeaderboard(0),
			new KothWinsLeaderboard(1),
			new KothWinsLeaderboard(2)
		], "koth_wins", 3, 5);
		
		$this->leaderboards["techits"] = new TechitsLeaderboard();
		$this->leaderboards["fishing"] = new FishingCatchesLeaderboard();

		
		$this->leaderboards["vote_streak"] = new VoteStreakLeaderboard();
		$this->leaderboards["top_island_level"] = new TopIslandLevelLeaderboard();

		$this->leaderboards["parkour_easy"] = new MultiLeaderboard([
			new ParkourAllTimeCompletionsLeaderboard("easy"),
			new ParkourBestTimeLeaderboard("easy")
		], "parkour_easy");

		$this->leaderboards["parkour_hard"] = new MultiLeaderboard([
			new ParkourAllTimeCompletionsLeaderboard("hard"),
			new ParkourBestTimeLeaderboard("hard")
		], "parkour_hard");
		
		$this->leaderboards["keys"] = new KeysOpenedLeaderboard();
		$this->leaderboards["keys_opened"] = new KeysLeaderboard();
	}

	/** @return Leaderboard[] */
	public function getLeaderboards() : array{
		return $this->leaderboards;
	}

	public function tick() : void{
		$this->ticks++;
		if($this->ticks >= self::UPDATE_TICKS){
			$this->ticks = 0;
			foreach($this->getLeaderboards() as $key => $leaderboard){
				if($leaderboard instanceof MysqlUpdate) $leaderboard->calculate();
			}
		}

		foreach($this->getLeaderboards() as $key => $leaderboard){
			if($leaderboard instanceof MultiLeaderboard){
				$leaderboard->tick();
			}
		}
	}

	public function changeLevel(Player $player, string $newlevel) : void{
		foreach($this->leaderboards as $leaderboard){
			$leaderboard->changeLevel($player, $newlevel);
		}
	}

	public function onJoin(Player $player) : void{
		unset($this->left[$player->getName()]);
		foreach($this->leaderboards as $leaderboard){
			if(!$leaderboard instanceof MysqlUpdate) $leaderboard->calculate();
			$leaderboard->spawn($player);
		}
	}

	public function onQuit(Player $player) : void{
		$this->left[$player->getName()] = true;
		foreach($this->leaderboards as $leaderboard){
			$leaderboard->despawn($player);
			if($leaderboard->isOn($player) && $leaderboard instanceof MysqlUpdate) $leaderboard->calculate();
		}
	}

}