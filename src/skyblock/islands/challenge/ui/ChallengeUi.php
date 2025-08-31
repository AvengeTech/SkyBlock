<?php namespace skyblock\islands\challenge\ui;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class ChallengeUi extends SimpleForm{

	public function __construct(Player $player, public bool $back = false){
		/** @var SkyBlockPlayer $player */
		parent::__construct("Challenges", "View island challenges below!");

		$isession = ($gs = $player->getGameSession())->getIslands();
		$island = $isession->getIslandAt();
		$csession = $island->getChallengeManager();

		if($isession->atIsland()){
			for($i = 1; $i <= min(20, $island->getSizeLevel()); $i++){
				$challenges = $csession->getLevelSession($i)->getChallenges();
				$total = count(SkyBlock::getInstance()->getIslands()->getChallenges()->getChallenges($i));
				$complete = 0;
				foreach($challenges as $challenge){
					if($challenge->isCompleted()) $complete++;
				}
				$this->addButton(new Button("Level " . $i . " Challenges" . "\n" . $complete . "/" . $total . " completed"));
			}
			if($back){
				$this->addButton(new Button("Go back"));
			}
		}
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$isession = ($gs = $player->getGameSession())->getIslands();
		if(!$isession->atIsland()){
			$player->sendMessage(TextFormat::RI . "You are no longer at an island.");
			return;
		}
		$island = $isession->getIslandAt();
		$csession = $island->getChallengeManager();
		$level = $response + 1;

		if($level > 20 || $island->getSizeLevel() < $level){
			if($this->back){
				$player->showModal(new IslandInfoUi($player, $island));
				return;
			}
			$player->sendMessage(TextFormat::RI . "This island is not high enough level to access these challenges!");
			return;
		}

		$player->showModal(new LevelsUi($level, $csession, $this->back, ($player->isSn3ak() || $player->isTier3())));
	}

}