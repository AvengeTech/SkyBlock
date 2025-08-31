<?php

namespace skyblock\generators\ui;

use core\AtPlayer;
use core\ui\elements\customForm\Dropdown;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use skyblock\generators\item\Extender;
use skyblock\generators\item\HorizontalExtender;
use skyblock\generators\item\VerticalExtender;
use skyblock\generators\tile\AutoMiner;
use skyblock\generators\tile\OreGenerator;
use skyblock\islands\permission\Permissions;
use skyblock\SkyBlockPlayer;

class RemoveExtenderUi extends CustomForm{

	/** @var Extender[] $extenders */
	private array $extenders = [];

	public function __construct(
		private OreGenerator|AutoMiner $tile
	){
		parent::__construct("Remove Extender");

		$this->addElement(new Label("Select which extender you would like to remove below."));

		if($tile->getHorizontalExtender() > 0){
			$this->extenders[] = ItemRegistry::HORIZONTAL_EXTENDER()->setup($tile->getHorizontalExtender())->init();
		}

		if($tile->getVerticalExtender() > 0){
			$this->extenders[] = ItemRegistry::VERTICAL_EXTENDER()->setup($tile->getVerticalExtender())->init();
		}

		$names = [];

		foreach($this->extenders as $extender){
			$names[] = $extender->getName() . TF::WHITE . "(LVL: " . TF::AQUA . $extender->getLevel() . TF::WHITE . ")";
		}

		$this->addElement(new Dropdown("Extender", $names));
	}

	/** @param SkyBlockPlayer $player */
	public function handle($response, AtPlayer $player){
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

		$extender = $this->extenders[$response[1]];

		if(!$player->getInventory()->canAddItem($extender)){
			$player->sendMessage(TF::RI . "You do not have any space in your inventory!");
			return;
		}

		$player->getInventory()->addItem($extender);

		if($extender instanceof HorizontalExtender){
			$tile->setHorizontalExtender($tile->getHorizontalExtender() - 1);
		}elseif($extender instanceof VerticalExtender){
			$tile->setVerticalExtender($tile->getVerticalExtender() - 1);
		}

		$player->sendMessage(TF::GI . "Removed " . $extender->getName() . TF::RESET . TF::GRAY . " from " . ($tile instanceof OreGenerator ? TF::AQUA . "Ore Generator" : TF::DARK_PURPLE . "Autominer"));
	}
}