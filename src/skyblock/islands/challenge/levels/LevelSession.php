<?php namespace skyblock\islands\challenge\levels;

use pocketmine\nbt\tag\CompoundTag;

use skyblock\SkyBlock;
use skyblock\islands\challenge\Challenge;

abstract class LevelSession{

	public $challenges = [];

	public function __construct(CompoundTag $nbt){
		$challenges = SkyBlock::getInstance()->getIslands()->getChallenges()->getChallenges(($level = $this->getLevel()));

		foreach($nbt->getValue() as $cdata){
			if(($id = $cdata->getInt("id", -1)) !== -1){
				/** @var Challenge $chal */
				$chal = $challenges[$id];
				$chal->setProgressViaNBT($cdata);
				$chal->fixChallengeData();
				$this->challenges[$id] = $chal;
			}
		}
		$this->check();
	}

	public function check() : void{
		$challenges = SkyBlock::getInstance()->getIslands()->getChallenges()->getChallenges($this->getLevel());
		$tchallenges = $this->getChallenges();
		foreach($challenges as $challenge){
			foreach($tchallenges as $tchallenge){
				if($tchallenge->getId() == $challenge->getId()){
					continue 2;
				}
			}
			$this->challenges[$challenge->getId()] = $challenge;
		}
		ksort($this->challenges);
	}

	/** @return Challenge[] */
	public function getChallenges() : array{
		return $this->challenges;
	}

	public function getChallengeById(int $id) : ?Challenge{
		return $this->challenges[$id] ?? null;
	}

	public function getSaveNBT() : CompoundTag{
		$nbt = CompoundTag::create();
		foreach($this->getChallenges() as $id => $challenge){
			$nbt->setTag("challenge_" . $challenge->getId(), $challenge->getProgressNBT());
		}
		return $nbt;
	}

	abstract function getLevel() : int;

}