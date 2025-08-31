<?php namespace skyblock\islands\ui\manage;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;

class LeaveIslandUi extends ModalWindow{

	public function __construct(public Island $island){
		parent::__construct(
			"Leave island",
			"Are you sure you'd like to leave this island? This will remove your permissions and you will no longer have access to this island (unless it becomes public.)",
			"Leave island", "Go back"
		);
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null){
			$player->sendMessage(TextFormat::RI . "You aren't a member of this island!");
			return;
		}
		if($response){
			$island->getPermissions()->removePermissions($pp);
			$island->unloadElsewhere();
			$player->gotoSpawn();
			$player->setAllowFlight(true);
		}else{
			$player->showModal(new IslandInfoUi($player, $island));
		}
	}

}