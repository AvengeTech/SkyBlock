<?php namespace skyblock\utils\stats;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
    SkyBlockPlayer,
    SkyBlockSession
};

use core\Core;
use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class StatSearchUi extends CustomForm{

	public function __construct(string $message = "", bool $error = true){
		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Enter a gamertag to view stats of a player!"
		));
		$this->addElement(new Input("Gamertag", "sn3akrr"));
	}

	public function handle($response, Player $player){
		$name = $response[1];
		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($player) : void{
			/** @var SkyBlockPlayer $player */
			if(!$player->isConnected()) return;
			if(!$user->valid()){
				$player->showModal(new StatSearchUi("Player never seen!"));
				return;
			}
			SkyBlock::getInstance()->getSessionManager()->useSession($user, function(SkyBlockSession $session) use($player) : void{
				if(!$player->isConnected()) return;
				$player->showModal(new StatsUi($session));
			});
		});
	}

}