<?php namespace skyblock\islands\ui;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\ui\access\{
	MyIslandsUi,
	PublicIslandsUi,
	staff\VisitAnyIslandUi
};
use skyblock\islands\ui\help\CommandHelpUi;
use skyblock\islands\ui\manage\invite\MyInvitesUi;
use skyblock\islands\ui\manage\IslandInfoUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class IslandsUi extends SimpleForm{

	public function __construct(Player $player, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct("Islands", ($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Select an option below to get started!");

		$session = $player->getGameSession()->getIslands();
		if($session->atIsland()){
			$this->addButton(new Button("Open island menu"));
		}
		if(count($session->getPermissions()) > 0){
			$this->addButton(new Button("My islands"));
		}
		$this->addButton(new Button("Create new island"));
		$this->addButton(new Button("Island invites"));
		$this->addButton(new Button("Public islands"));
		$this->addButton(new Button("Help"));
		if($player->isStaff()){
			$this->addButton(new Button(TextFormat::ICON_MOD . " Visit any island"));
		}
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getIslands();
		if($session->atIsland()){
			if($response == 0){
				$player->showModal(new IslandInfoUi($player, $session->getIslandAt()));
				return;
			}
			$response--;
		}
		if(count($session->getPermissions()) > 0){
			if($response == 0){
				$worlds = [];
				foreach($session->getPermissions() as $permission){
					$worlds[] = $permission->getIslandWorld();
				}
				SkyBlock::getInstance()->getIslands()->getIslandManager()->loadIslands($worlds, function(array $islands) use($player) : void{
					if(!$player->isConnected()) return;
					$player->showModal(new MyIslandsUi($player, $islands));
				});
				return;
			}
			$response--;
		}
		if($response == 0){
			if(count($session->getOwnerPermissions()) >= ($t = $session->getTotalAllowedIslands($player))){
				$player->showModal(new IslandsUi($player, "You already have the max amount of islands." . ($t < 2 ? PHP_EOL . PHP_EOL . "Purchase enderdragon " . TextFormat::ICON_ENDERDRAGON . " rank to access a second island! " . TextFormat::YELLOW . "store.avengetech.net" : "")));
				return;
			}
			$player->showModal(new CreateIslandUi($player));
			return;
		}
		if($response == 1){
			if(count(SkyBlock::getInstance()->getIslands()->getInviteManager()->getInvitesFor($player)) === 0){
				$player->showModal(new IslandsUi($player, "You have no incoming island invites!"));
				return;
			}
			$player->showModal(new MyInvitesUi($player));
			return;
		}
		if($response == 2){
			SkyBlock::getInstance()->getIslands()->getIslandManager()->getAllPublicIslands(function(array $islands) use($player) : void{
				if(!$player->isConnected()) return;
				if(count($islands) === 0){
					$player->showModal(new IslandsUi($player, "No public islands are currently available"));
					return;
				}
				$player->showModal(new PublicIslandsUi($player, $islands));
			});
		}
		if($response == 3){
			$player->showModal(new CommandHelpUi($player));
			return;
		}
		if($response == 4 && $player->isStaff()){
			$player->showModal(new VisitAnyIslandUi());
			return;
		}
	}

}