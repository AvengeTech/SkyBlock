<?php namespace skyblock\islands\ui\manage;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;

use core\ui\windows\ModalWindow;
use core\utils\TextFormat;

class DeleteIslandUi extends ModalWindow{

	public function __construct(public Island $island, public bool $warning = false){
		parent::__construct(
			"Delete",
			($warning ?
				"ARE YOU SURE YOU'RE SURE??? THAT BUTTON PRESS DIDN'T SEEM VERY CONFIDENT!" :
				"Are you sure you want to delete this island? This action CANNOT be undone."
			),
			$warning ? "I'M 100%% SURE" : "Delete island", "Go back"
		);
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit this island's permissions!");
			return;
		}
		if(!$pp->isOwner()){
			$player->showModal(new IslandManageUi($player, $island, "Only the owner of this island can delete it"));
			return;
		}
		if($response){
			if($this->warning){
				SkyBlock::getInstance()->getIslands()->getIslandManager()->deleteIsland($island);
			}else{
				$player->showModal(new DeleteIslandUi($island, true));
			}
		}else{
			$player->showModal(new IslandManageUi($player, $island));
		}
	}

}