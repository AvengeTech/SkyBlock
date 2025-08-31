<?php namespace skyblock\islands\ui\access;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\{
	Island,
};
use skyblock\islands\ui\IslandsUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class MyIslandsUi extends SimpleForm{

	public array $myIslands = [];
	public array $otherIslands = [];

	public int $split;

	public function __construct(Player $player, public array $islands = [], string $error = ""){
		parent::__construct("My islands", ($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Select which island you'd like to travel to!");
		foreach($islands as $island){
			if($island->getPermissions()->getOwner()->getUser()->belongsTo($player)){
				$this->myIslands[] = $island;
			}else{
				$this->otherIslands[] = $island;
			}
		}
		$count = $this->split = count($this->myIslands);
		if($count > 0){
			foreach($this->myIslands as $island){
				$this->addButton(new Button($island->getName()));
			}
		}
		if(count($this->otherIslands) !== 0){
			$this->addButton(new Button("Friendly islands"));
			foreach($this->otherIslands as $island){
				$this->addButton(new Button($island->getName() . PHP_EOL . "[" . $island->getPermissions()->getOwner()->getUser()->getGamertag() . "]"));
			}
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if($response === $this->split){
			$player->showModal(new IslandsUi($player));
			return;
		}elseif($response > $this->split){
			$island = $this->otherIslands[$response - $this->split - 1] ?? null;
		}else{
			$island = $this->myIslands[$response] ?? null;
		}
		if($island instanceof Island){
			SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($player, $island);
			return;
		}
		$player->showModal(new IslandsUi($player));
	}
	
}