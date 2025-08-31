<?php namespace skyblock\generators\ui;

use pocketmine\player\Player;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat as TF;

use skyblock\islands\permission\Permissions;
use skyblock\generators\tile\OreGenerator;
use skyblock\generators\Structure;
use skyblock\SkyBlockPlayer;

class OreGeneratorUi extends SimpleForm{

	public function __construct(
		Player $player, 
		private OreGenerator $tile
	){
		/** @var SkyBlockPlayer $player */
		$block = $tile->getTypeBlock();
		if($tile->getLevel() === 0) return;
		
		parent::__construct(
			"Ore generator",
			TF::GRAY . "Type: " . $block->getName() . "\n" .
			TF::GRAY . "Level: " . TF::YELLOW . $tile->getLevel() . TF::WHITE . " (" . Structure::RATES[Structure::TYPE_ORE_GENERATOR][$tile->getLevel()] . " second rate)" . "\n" .
			TF::GRAY . "Boost: " . ($tile->hasBoost() ? TF::YELLOW . $tile->getBoost() . " blocks left" : "None") . "\n" .
			TF::GRAY . "Extensions: " . ($tile->getHorizontalExtender() > 0 || $tile->getVerticalExtender() > 0 ? "\n" . 
			TF::GRAY . " - Horizontal: " . TF::DARK_GREEN . Structure::EXTENDER[$tile->getHorizontalExtender()] . " blocks\n" . 
			TF::GRAY . " - Vertical: " . TF::GREEN . Structure::EXTENDER[$tile->getVerticalExtender()] . " blocks"
			: "None") . "\n\n" .
			TF::GRAY . "Solidifer: " . ($tile->getSolidifierLevel() > 0 ? "\n" . 
			TF::GRAY . " - Level: " . TF::DARK_PURPLE . $tile->getSolidifierLevel() . "\n" . 
			TF::GRAY . " - Runs: " . TF::LIGHT_PURPLE . $tile->getSolidifierRuns()
			: "None") . "\n\n" .

			TF::WHITE . "Each upgrade reduces the spawn rate by 1 second." . "\n" .
			"Each boost cuts the spawn rate in half for 1 block" . "\n\n" .
			($tile->canLevelUp() ? "Tap an option below!" : "Your ore generator has reached the max level!")
		);

		if($tile->canLevelUp()){
			$next = $tile->getLevel() + 1;
			$cost = Structure::UPGRADE_COSTS[Structure::TYPE_ORE_GENERATOR][$tile->getType()][$next];
			$this->addButton(new Button("Upgrade (" . number_format($cost) . " techits)" . "\n" . ($cost > $player->getTechits() ? TF::RED . "CANNOT AFFORD" : "")));
		}

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
		
		if($tile->canLevelUp()){
			if($response == 0){
				if($player->getTechits() < $tile->getNextLevelPrice()){
					$player->sendMessage(TF::RI . "You don't have enough techits!");
					return;
				}

				$tile->levelUp($player);
				$player->sendMessage(TF::GI . "Ore generator is now level " . $tile->getLevel() . "!");
				return;
			}
		}

		if(
			$tile->getVerticalExtender() > 0 || 
			$tile->getHorizontalExtender() > 0
		){
			if($response === ($tile->canLevelUp() ? 1 : 0)){
				$player->showModal(new RemoveExtenderUi($tile));
				return;
			}
		}
	}

}