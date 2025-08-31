<?php namespace skyblock\islands\ui\manage\invite;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\invite\Invite;
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\manage\IslandMembersUi;

use core\Core;
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class InvitePlayerUi extends CustomForm{

	public array $players = [];

	public function __construct(public Island $island, string $message = "", bool $error = true){
		parent::__construct("Invite player");
		foreach(Core::thisServer()->getSubServers(true, true) as $server){
			foreach($server->getCluster()->getPlayers() as $pl){
				if(
					!$island->isBlocked($pl->getUser()) &&
					$island->getPermissions()->getPermissionsBy($pl->getUser()) === null
				) $this->players[] = $pl->getGamertag();
			}
		}
		$this->addElement(new Label(($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Enter a player's username or select from the list below"));
		$this->addElement(new Input("Player (leave blank to select from list)", "username"));
		$this->addElement(new Dropdown("Online players", $this->players));
	}
	
	public function close(Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null || !$pp->getPermission(Permissions::EDIT_MEMBERS)){
			return;
		}
		
		$player->showModal(new IslandMembersUi($player, $island, true));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null || !$pp->getPermission(Permissions::EDIT_MEMBERS)){
			$player->sendMessage(TextFormat::RI . "You don't have permission to invite members!");
			return;
		}

		if(($name = $response[1]) == ""){
			$name = $this->players[$response[2]] ?? "";
		}
		if($name == ""){
			$player->showModal(new InvitePlayerUi($island, "Player never seen!"));
			return;
		}
		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($player, $island) : void{
			if(!$player->isConnected()) return;
			if(!$user->valid()){
				$player->showModal(new InvitePlayerUi($island, "Player never seen!"));
				return;
			}
			if($island->isBlocked($user)){
				$player->showModal(new InvitePlayerUi($island, "That player is blocked from this island!"));
				return;
			}
			if($island->getPermissions()->getPermissionsBy($user) !== null){
				$player->showModal(new InvitePlayerUi($island, "Player is already a member of this island!"));
				return;
			}

			$im = SkyBlock::getInstance()->getIslands()->getInviteManager();
			if($im->hasInviteTo($user, $island)){
				$player->showModal(new InvitePlayerUi($island, "Player already has an outgoing invite to this island!"));
				return;
			}

			$im->sendInvite(new Invite(
				$island,
				$player->getUser(),
				$user
			));

			$player->showModal(new IslandMembersUi($player, $island, true, $user->getGamertag() . " has been invited!", false));
		});
	}

}