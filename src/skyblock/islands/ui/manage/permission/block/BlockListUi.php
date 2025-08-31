<?php namespace skyblock\islands\ui\manage\permission\block;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class BlockListUi extends SimpleForm{

	public bool $edit = false;

	public array $blocked = [];

	public function __construct(Player $player, public Island $island, string $message = "", bool $error = true){
		parent::__construct(
			"Island block list",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Here is a list of players blocked from this island"
		);

		$perms = $island->getPermissions()->getPermissionsBy($player);
		$ce = $this->edit = $perms !== null && $perms->getPermission(Permissions::EDIT_BLOCK_LIST);

		if($ce) $this->addButton(new Button("Add player"));
		foreach($island->getBlockList() as $blocked){
			$this->addButton(new Button($blocked->getGamertag() . ($ce ? PHP_EOL . TextFormat::RED . "Tap to unblock" : "")));
			$this->blocked[] = $blocked;
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		if($this->edit){
			$pp = $island->getPermissions()->getPermissionsBy($player);
			if($pp === null || !$pp->getPermission(Permissions::EDIT_BLOCK_LIST)){
				$player->sendMessage(TextFormat::RI . "You don't have permission to edit this island's block list!");
				return;
			}

			if($response == 0){
				if(count($island->getBlockList()) >= Island::LIMIT_BLOCK_LIST){
					$player->showModal(new BlockListUi($player, $island, "You have reached the block limit! (" . Island::LIMIT_BLOCK_LIST . ")"));
					return;
				}
				$player->showModal(new BlockPlayerUi($island));
				return;
			}
			$response--;

			$blocked = $this->blocked[$response] ?? null;
			if($blocked !== null){
				$island->unblock($blocked);
				$player->showModal(new BlockListUi($player, $island, $blocked->getGamertag() . " has been unblocked!", false));
				return;
			}
		}
		$player->showModal(new IslandInfoUi($player, $island));
	}

}