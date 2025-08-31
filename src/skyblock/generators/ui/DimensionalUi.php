<?php namespace skyblock\generators\ui;

use pocketmine\player\Player;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

use skyblock\islands\permission\Permissions;
use skyblock\generators\tile\DimensionalTile;
use skyblock\generators\Structure;
use skyblock\SkyBlockPlayer;

class DimensionalUi extends SimpleForm{

	public function __construct(
		Player $player,
		private DimensionalTile $tile
	){
		/** @var SkyBlockPlayer $player */
		parent::__construct(
			"Dimensional block",
			"Level: " . $tile->getLevel() . " (" . Structure::RATES[Structure::TYPE_DIMENSIONAL_GENERATOR][$tile->getLevel()] . " second rate)" . PHP_EOL .
			"Boost: " . ($tile->hasBoost() ? $tile->getBoost() . " items left" : "None") . PHP_EOL . PHP_EOL .

			"Each upgrade reduces the spawn rate by 1 second." . PHP_EOL .
			"Each boost cuts the spawn rate in half for 1 item" . PHP_EOL . PHP_EOL .
			($tile->canLevelUp() ? "Tap an option below!" : "Your dimensional block has reached the max level!")
		);

		if($tile->canLevelUp()){
			$next = $tile->getLevel() + 1;
			$cost = Structure::UPGRADE_COSTS[Structure::TYPE_DIMENSIONAL_GENERATOR][$next];
			$this->addButton(new Button("Upgrade (" . number_format($cost) . " techits)" . PHP_EOL . ($cost > $player->getTechits() ? TextFormat::RED . "CANNOT AFFORD" : "")));
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$tile = $this->tile;
		if($tile->isClosed()){
			$player->sendMessage(TextFormat::RI . "This ore generator no longer exists");
			return;
		}

		$session = $player->getGameSession()->getIslands();
		if(!$session->atIsland()) return;
		$island = $session->getIslandAt();
		if($island->getWorldName() !== $tile->getPosition()->getWorld()->getDisplayName()) return;
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::EDIT_GEN_BLOCKS)){
			$player->sendMessage(TextFormat::RI . "You no longer have permission to edit gen blocks at this island!");
			return;
		}

		if($tile->canLevelUp()){
			if($response == 0){
				if($player->getTechits() < $tile->getNextLevelPrice()){
					$player->sendMessage(TextFormat::RI . "You don't have enough techits!");
					return;
				}
				$tile->levelUp($player);
				$player->sendMessage(TextFormat::GI . "Dimensional block is now level " . $tile->getLevel() . "!");
				return;
			}
		}

		$player->sendMessage(TextFormat::RI . "This ore generator has already reached the max level!");
	}

}