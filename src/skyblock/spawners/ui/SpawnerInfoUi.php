<?php namespace skyblock\spawners\ui;

use pocketmine\player\Player;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use skyblock\{
	SkyBlockPlayer
};
use skyblock\spawners\Spawners;
use skyblock\spawners\tile\Spawner;

use core\utils\TextFormat;

class SpawnerInfoUi extends SimpleForm{

	public $spawner;

	public function __construct(Spawner $spawner, string $message = ""){
		$this->spawner = $spawner->getPosition();

		parent::__construct("Spawner info", ($message !== "" ? TextFormat::GREEN . $message . PHP_EOL . PHP_EOL . TextFormat::WHITE : "") . "Spawner Level: " . $spawner->getSpawnerLevel() . "\n" . "Mob spawn: " . Spawners::LEVEL_MOB_NAMES[$spawner->levelEntity === -1 ? $spawner->getSpawnerLevel() : $spawner->levelEntity]);
		
		$this->addButton(new Button("Change mob"));
		$this->addButton(new Button($spawner->getSpawnerLevel() == 17 ? "Spawner is max level" : "Level up to level " . ($spawner->getSpawnerLevel() + 1) . "\n" . "Cost: " . number_format($spawner::LEVEL_PRICE[$spawner->getSpawnerLevel() + 1]) . "\n" . "Spawns: " . Spawners::LEVEL_MOB_NAMES[$spawner->getSpawnerLevel() + 1]));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$spawner = $this->spawner->getWorld()->getTile($this->spawner);
		if(!$spawner instanceof Spawner){
			$player->sendMessage(TextFormat::RI . "Spawner no longer exists.");
			return;
		}
		if($response === 0){
			$player->showModal(new ChangeMobUi($player, $spawner));
			return;
		}
		if($response === 1){
			if(!$spawner->canLevelUp($player)){
				$msg = TextFormat::RI  . "You cannot level up this spawner.";
				if($spawner->getSpawnerLevel() === 17){
					$msg .= " This spawner has already reached the max level!";
				}else{
					$msg .= " Not enough techits!";
				}
				$player->sendMessage($msg);
				return;
			}
			$spawner->levelUp($player);
			$player->sendMessage(TextFormat::GI . "Spawner has been leveled up! You unlocked a new mob type");
		}
	}

}