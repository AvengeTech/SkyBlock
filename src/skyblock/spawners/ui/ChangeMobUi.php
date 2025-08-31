<?php namespace skyblock\spawners\ui;

use pocketmine\player\Player;

use skyblock\{
	SkyBlockPlayer
};
use skyblock\spawners\tile\Spawner;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class ChangeMobUi extends SimpleForm{

	public $spawner;
	public int $level;

	const MOBS = [
		1 => "Pig",
		2 => "Chicken",
		3 => "Sheep",
		4 => "Cow",
		5 => "Spider",
		6 => "Cave Spider",
		7 => "Skeleton",
		8 => "Zombie",
		9 => "Husk",
		10 => "Creeper",
		11 => "Mooshroom",
		12 => "WitherSkeleton",
		13 => "Blaze",
		14 => "Breeze",
		15 => "Enderman",
		16 => "Witch",
		17 => "Iron Golem",
	];

	public function __construct(Player $player, Spawner $spawner){
		parent::__construct("Change mob", "Select the type of mob you'd like this spawner to spawn!");

		$this->spawner = $spawner->getPosition();
		$this->level = $spawner->getSpawnerLevel();

		for($i = 1; $i <= $this->level; $i++){
			$this->addButton(new Button(($i === ($spawner->levelEntity === -1 ? $spawner->getSpawnerLevel() : $spawner->levelEntity) ? TextFormat::GREEN : "") . self::MOBS[$i]));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$spawner = $this->spawner->getWorld()->getTile($this->spawner);
		if(!$spawner instanceof Spawner){
			$player->sendMessage(TextFormat::RI . "Spawner no longer exists.");
			return;
		}
		//check island perms
		if($response < $this->level){
			$level = $response + 1;
			$spawner->setLevelEntity($level === $spawner->getSpawnerLevel() ? -1 : $level);
			$player->showModal(new SpawnerInfoUi($spawner, "Mob has been updated!"));
			return;
		}
		$player->showModal(new SpawnerInfoUi($spawner));
	}

}