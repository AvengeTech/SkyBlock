<?php namespace skyblock\islands\ui\manage\permission\block;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;

use core\Core;
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class BlockPlayerUi extends CustomForm{

	public array $players = [];

	public function __construct(public Island $island){
		parent::__construct("Block player");
		foreach(Core::thisServer()->getSubServers(true, true) as $server){
			foreach($server->getCluster()->getPlayers() as $pl){
				if(
					!$island->isBlocked($pl->getUser()) &&
					$island->getPermissions()->getPermissionsBy($pl->getUser()) === null
				) $this->players[] = $pl->getGamertag();
			}
		}
		$this->addElement(new Label("Enter a player's username or select from the list below"));
		$this->addElement(new Input("Player (leave blank to select from list)", "username"));
		$this->addElement(new Dropdown("Online players", $this->players));
	}

	public function close(Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null || !$pp->getPermission(Permissions::EDIT_BLOCK_LIST)){
			return;
		}

		$player->showModal(new BlockListUi($player, $island));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null || !$pp->getPermission(Permissions::EDIT_BLOCK_LIST)){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit this island's block list!");
			return;
		}
		
		if(($name = $response[1]) == ""){
			$name = $this->players[$response[2]] ?? "";
		}
		if($name == ""){
			$player->showModal(new BlockListUi($player, $island, "Player never seen!"));
			return;
		}
		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($player, $island) : void{
			if(!$player->isConnected()) return;
			if(!$user->valid()){
				$player->showModal(new BlockListUi($player, $island, "Player never seen!"));
				return;
			}
			if($island->isBlocked($user)){
				$player->showModal(new BlockListUi($player, $island, "That player is already blocked from this island!"));
				return;
			}
			if($island->getPermissions()->getPermissionsBy($user) !== null){
				$player->showModal(new BlockListUi($player, $island, "You cannot block members of this island."));
				return;
			}
			$island->block($user);
			$player->showModal(new BlockListUi($player, $island, $user->getGamertag() . " has been blocked!", false));
		});
	}

}