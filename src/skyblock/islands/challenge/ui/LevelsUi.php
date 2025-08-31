<?php namespace skyblock\islands\challenge\ui;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\challenge\ChallengeManager;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;
use skyblock\islands\challenge\Challenge;

class LevelsUi extends SimpleForm{

	/** @var Challenge[] $challenges */
	public array $challenges = [];

	public function __construct(public int $level, public ChallengeManager $session, public bool $back = false, bool $isTier3 = false){
		$this->challenges = $challenges = array_values($session->getLevelSession($level)->getChallenges());
		$total = count(SkyBlock::getInstance()->getIslands()->getChallenges()->getChallenges($level));
		$complete = 0;
		foreach($challenges as $challenge){
			if($challenge->isCompleted()) $complete++;
		}

		parent::__construct("Level " . $level . " Challenges", "You have " . $complete . "/" . $total . " challenges completed.");

		foreach($this->challenges as $challenge){
			$this->addButton(new Button(($challenge->isCompleted() ? TextFormat::GREEN : TextFormat::RED) . $challenge->getName()  . ($isTier3 ? " (ID: " . $challenge->getId() . ")" : "")));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		foreach($this->challenges as $key => $challenge){
			if($key == $response){
				$player->showModal(new SingleUi($challenge, $this));
				return;
			}
		}
		$player->showModal(new ChallengeUi($player, $this->back));
	}

}