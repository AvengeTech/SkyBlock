<?php namespace skyblock\islands\challenge\ui;

use pocketmine\player\Player;

use skyblock\islands\challenge\Challenge;
use skyblock\SkyBlockPlayer;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class SingleUi extends SimpleForm{

	public function __construct(public Challenge $challenge, public LevelsUi $prev){
		$string = "";
		foreach($challenge->getProgress() as $progress => $data){
			if(is_array($data)){
				$dstring = $data["progress"] . "/" . $data["needed"];
				$string .= ucfirst($progress) . ": " . $dstring . PHP_EOL;
			}
		}
		$string = rtrim($string);
		if($string != "") $string .= PHP_EOL;
		if($challenge->isCompleted()){
			$string .= "Completed: YES" . PHP_EOL . "Completed by: " . $challenge->getCompletedBy() . " (" . $challenge->getCompletedWhenFormatted() . ")" . PHP_EOL;
		}else{
			$string .= "Completed: NO";
		}
		parent::__construct(
			$challenge->getName(),
			$challenge->getDescription() . PHP_EOL . PHP_EOL .
			"Challenge progress:" . PHP_EOL .
			$string . PHP_EOL .
			"Completed prize: " . number_format($challenge->getTechits()) . " techits" . PHP_EOL . PHP_EOL .
			"Difficulty: " . $challenge->getDifficultyString()
		);

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$player->showModal($this->prev);
	}

}