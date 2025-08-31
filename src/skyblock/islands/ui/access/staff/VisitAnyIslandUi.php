<?php namespace skyblock\islands\ui\access\staff;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockSession
};

use core\Core;
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class VisitAnyIslandUi extends CustomForm{

	public array $players = [];

	public function __construct(string $message = "", bool $error = true){
		parent::__construct("Visit any island");
		foreach(Core::thisServer()->getSubServers(true, true) as $server){
			foreach($server->getCluster()->getPlayers() as $pl){
				$this->players[] = $pl->getGamertag();
			}
		}
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Enter a player's username or select from the list below"
		));
		$this->addElement(new Input("Player (leave blank to select from list)", "username"));
		$this->addElement(new Dropdown("Online players", $this->players));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(!$player->isStaff()){
			$player->sendMessage(TextFormat::RI . "You do not have permission to access any island!");
			return;
		}

		if(($name = $response[1]) == ""){
			$name = $this->players[$response[2]] ?? "";
		}
		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($player) : void{
			if(!$player->isConnected()) return;
			if(!$user->valid()){
				$player->showModal(new VisitAnyIslandUi("Player never seen!"));
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($player, $user) : void{
				if(!$player->isConnected()) return;
				if(count($session->getIslands()->getPermissions()) === 0){
					$player->showModal(new VisitAnyIslandUi("This player has no islands!"));
					return;
				}
				$worlds = [];
				foreach($session->getIslands()->getPermissions() as $permission){
					$worlds[] = $permission->getIslandWorld();
				}
				SkyBlock::getInstance()->getIslands()->getIslandManager()->loadIslands($worlds, function(array $islands) use($player, $user) : void{
					if(!$player->isConnected()) return;
					$player->showModal(new PlayerIslandsUi($user, $islands));
				});
			});
		});
	}

}