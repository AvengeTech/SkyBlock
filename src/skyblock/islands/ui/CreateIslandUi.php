<?php namespace skyblock\islands\ui;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;

use core\network\protocol\PlayerLoadActionPacket;
use core\network\server\SubServer;
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class CreateIslandUi extends CustomForm{

	public function __construct(Player $player, string $error = "") {
		/** @var SkyBlockPlayer $player */
		parent::__construct("Create island");

		$this->addElement(new Label(($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Enter the details of your island below!"));
		$this->addElement(new Input("Island name", "My island"));
		$this->addElement(new Dropdown("Island type", ["Basic", TextFormat::ICON_ENDERMAN . " Flat Top"]));
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$name = $response[1];
		$type = $response[2];

		if(strlen($name) < 1 || strlen($name) > Island::LIMIT_NAME){
			$player->showModal(new CreateIslandUi($player, "Island name must be between 1-" . Island::LIMIT_NAME . " characters"));
			return;
		}

		if($type >= 1){
			if($player->getRankHierarchy() < $player->getRankHierarchy("enderman")){
				$player->showModal(new CreateIslandUi($player, "You must have at least enderman rank to use this island generator! Purchase it at " . TextFormat::YELLOW . "store.avengetech.net"));
				return;
			}
		}

		$session = $player->getGameSession()->getIslands();
		if(count($session->getOwnerPermissions()) >= $session->getTotalAllowedIslands($player)){
			$player->showModal(new IslandsUi($player, "You cannot create anymore islands."));
			return;
		}
		SkyBlock::getInstance()->getIslands()->getIslandManager()->createIsland($player, $name, $type, function(Island $island) use($player) : void{
			if(!$player->isConnected()) return;

			$im = SkyBlock::getInstance()->getIslands()->getIslandManager();
			if($im->loadIslandsOnMain()){
				$im->gotoIsland($player, $island);
				return;
			}
			
			$server = $im->findIslandWorldServer($island->getWorldName());
			if(!$server instanceof SubServer){
				$server = $im->findLeastPopulatedIslandServer();
				if(!$server instanceof SubServer){
					$player->sendMessage(TextFormat::RI . "No server available to load island at... Please try again soon!");
					return;
				}
			}
			(new PlayerLoadActionPacket([
				"player" => $player->getName(),
				"server" => $server->getIdentifier(),
				"action" => "island",
				"actionData" => ["world" => $island->getWorldName()]
			]))->queue();
			$player->getGameSession()->save(true, function($session) use($player, $server) : void{
				if($player->isConnected()){
					$server->transfer($player, TextFormat::GI . "Welcome to your new island!");
					$server->sendSessionSavedPacket($player, 1);
				}
				$player->getGameSession()->getSessionManager()->removeSession($player);
			});
			$player->sendMessage(TextFormat::YELLOW . "Saving game session data...");
		}, false);
	}

}