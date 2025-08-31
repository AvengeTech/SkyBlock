<?php namespace skyblock\islands\challenge;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\BigEndianNbtSerializer as BigEndianNBTStream;

use skyblock\SkyBlock;
use skyblock\islands\Island;
use skyblock\islands\challenge\levels\{
	LevelSession,

	level1\Level1Session,
	level2\Level2Session,
	level3\Level3Session,
	level4\Level4Session,
	level5\Level5Session,
	level6\Level6Session,
	level7\Level7Session,
	level8\Level8Session,
	level9\Level9Session,
	level10\Level10Session,
	level11\Level11Session,
	level12\Level12Session,
	level13\Level13Session,
	level14\Level14Session,
	level15\Level15Session
};

use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};

class ChallengeManager{

	const CLASS_NAMESPACE = "\skyblock\islands\challenge\levels\level";
	const TOTAL_LEVELS = 20;
	
	/** @var LevelSession[] $levelSessions */
	public array $levelSessions = [];

	public bool $saving = false;
	
	public function __construct(public Island $island, ?string $data = null){
		$this->setup($data);
	}

	public function getIsland() : Island{
		return $this->island;
	}
	
	public function getLevelSession(int $level) : LevelSession{
		return $this->levelSessions[$level] ?? $this->createLevelSession($level);
	}

	public function hasLevelUnlocked(int $level) : bool{
		return isset($this->levelSessions[$level]);
	}

	public function createLevelSession(int $level, ?CompoundTag $tag = null) : LevelSession{
		$class = self::CLASS_NAMESPACE . $level . "\Level" . $level . "Session";
		$this->levelSessions[$level] = new $class($tag == null ? CompoundTag::create() : $tag);
		return $this->levelSessions[$level];
	}

	public function getTotalChallengesCompleted() : int{
		$count = 0;
		foreach($this->levelSessions as $level => $session){
			foreach($session->getChallenges() as $challenge){
				if($challenge->isCompleted()){
					$count++;
				}
			}
		}
		return $count;
	}
	
	public function getChallengesNeededToLevelUp() : int{
		$level = min($this->getIsland()->getSizeLevel(), 20);
		$challengeCount = 0;

		foreach($this->levelSessions as $session){
			$challengeCount += max(count($session->getChallenges()) - 2, 1);
		}

		$challenges = match($level){
			1 => 9,
			2 => 22,
			3 => 38,
			4 => 52,
			5 => 68,
			6 => 85,
			7 => 95,
			8 => 110,
			9 => 125,
			10 => 135,
			default => $challengeCount
		};

		return min($challenges, $challengeCount);
	}

	public function setup(string $data = null) : void{
		$max = min(self::TOTAL_LEVELS, $this->getIsland()->getSizeLevel());
		if($data !== null){
			try{
				$nbt = unserialize(zlib_decode($data));
				for($i = 1; $i <= $max; $i++){
					if($nbt->getTag("level_" . $i) !== null){
						$this->createLevelSession($i, $nbt->getTag("level_" . $i));
					}
				}
			}catch(\Exception $e){
				for($i = 1; $i <= $max; $i++){
					$this->createLevelSession($i);
				}
			}
		}else{
			for($i = 1; $i <= $max; $i++){
				$this->createLevelSession($i);
			}
		}
	}
	
	public function toString() : string{
		$nbt = CompoundTag::create();
		foreach($this->levelSessions as $level => $session){
			$nbt->setTag("level_" . $session->getLevel(), $session->getSaveNBT());
		}
		return zlib_encode(serialize($nbt), ZLIB_ENCODING_DEFLATE, 1);
	}
	
	public function save(bool $async = true) : void{
		if($async){
			$this->saving = true;
			SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
				new MySqlRequest("save_island_challenges_" . $this->getIsland()->getWorldName(), new MySqlQuery("main",
					"INSERT INTO island_challenges(
						world, challenges
					) VALUES(?, ?) ON DUPLICATE KEY UPDATE
						challenges=VALUES(challenges)",
					[
						$this->getIsland()->getWorldName(), $this->toString()
					]
				)),
				function(MySqlRequest $request){
					$this->saving = false;
				}
			);
		}else{
			$world = $this->getIsland()->getWorldName();
			$challenges = $this->toString();

			$db = SkyBlock::getInstance()->getSessionManager()->getDatabase();
			$stmt = $db->prepare(
				"INSERT INTO island_challenges(
					world, challenges
				) VALUES(?, ?) ON DUPLICATE KEY UPDATE
					challenges=VALUES(challenges)"
			);
			$stmt->bind_param("ss", $world, $challenges);
			$stmt->execute();
			$stmt->close();
		}
	}

	public function isSaving() : bool{
		return $this->saving;
	}

	public function delete() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest("delete_challenges_" . ($name = $this->getIsland()->getWorldName()), new MySqlQuery("main",
				"DELETE FROM island_challenges WHERE world=?",
				[$name]
			)),
			function(MySqlRequest $request){}
		);
	}

}