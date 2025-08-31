<?php namespace skyblock\generators\ui;

use core\ui\elements\simpleForm\Button;
use pocketmine\player\Player;

use core\ui\windows\SimpleForm;
use core\utils\TextFormat as TF;
use skyblock\generators\Structure;
use skyblock\islands\permission\Permissions;
use skyblock\generators\tile\AutoMiner;
use skyblock\SkyBlockPlayer;

class AutoMinerUi extends SimpleForm{

	public function __construct(
		private AutoMiner $tile
	){
		parent::__construct(
			"Auto Miner",
			TF::GRAY . "Extensions: " . ($tile->getHorizontalExtender() > 0 || $tile->getVerticalExtender() > 0 ? "\n" . 
			TF::GRAY . " - Horizontal: " . TF::DARK_GREEN . Structure::EXTENDER[$tile->getHorizontalExtender()] . " blocks\n" . 
			TF::GRAY . " - Vertical: " . TF::GREEN . Structure::EXTENDER[$tile->getVerticalExtender()] . " blocks"
			: "None")
		);

		if(
			$tile->getVerticalExtender() > 0 || 
			$tile->getHorizontalExtender() > 0
		) $this->addButton(new Button("Remove Extender"));
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, Player $player){
		$tile = $this->tile;

		if($tile->isClosed()){
			$player->sendMessage(TF::RI . "This ore generator no longer exists");
			return;
		}

		$session = $player->getGameSession()->getIslands();

		if(!$session->atIsland()) return;

		$island = $session->getIslandAt();

		if($island->getWorldName() !== $tile->getPosition()->getWorld()->getDisplayName()) return;

		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if(!$perm->getPermission(Permissions::EDIT_GEN_BLOCKS)){
			$player->sendMessage(TF::RI . "You no longer have permission to edit gen blocks at this island!");
			return;
		}

		if(
			$tile->getVerticalExtender() > 0 || 
			$tile->getHorizontalExtender() > 0
		){
			if($response === 0){
				$player->showModal(new RemoveExtenderUi($tile));
				return;
			}
		}
	}
}