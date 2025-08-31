<?php namespace skyblock\islands\ui\manage\invite;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\invite\Invite;
use skyblock\islands\ui\IslandsUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

class ViewInviteUi extends SimpleForm{

	public function __construct(Player $player, public Invite $invite) {
		/** @var SkyBlockPlayer $player */
		$island = $invite->getIsland();
		parent::__construct("Island invite",
			"Island owner: " . $island->getPermissions()->getOwner()->getUser()->getGamertag() . PHP_EOL . PHP_EOL .

			"Name: " . $island->getName() . PHP_EOL .
			"Description: " . $island->getDescription() . PHP_EOL .
			"Level: " . $island->getSizeLevel() . PHP_EOL . PHP_EOL .

			"Gen blocks: " . $island->getGenCount() . "/" . $island->getMaxGenCount() . PHP_EOL .
			"Spawners: " . $island->getSpawnerCount() . "/" . $island->getMaxSpawnerCount() . PHP_EOL .
			"Hoppers: " . $island->getHopperCount() . "/" . $island->getMaxHopperCount() . PHP_EOL . PHP_EOL .

			"Date created: " . $island->getCreatedFormatted() . PHP_EOL .
			"Type: " . SkyBlock::getInstance()->getIslands()->getIslandManager()->getGeneratorInfo($island->getIslandType())->getName() . PHP_EOL . PHP_EOL .

			"What would you like to do with this invite?"
		);
		
		$this->addButton(new Button("Accept"));
		$this->addButton(new Button("Deny"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		if(!($im = SkyBlock::getInstance()->getIslands()->getInviteManager())->hasInviteTo($player, $this->invite->getIsland())){
			$player->showModal(new MyInvitesUi($player, "This invite no longer exists!"));
			return;
		}
		
		if($response == 0){
			$this->invite->accept();
			$im->removeInvite($player->getName(), $this->invite->getIsland()->getWorldName());
			$player->showModal(new IslandsUi($player, "Island invite has been accepted!", false));
			return;
		}
		if($response == 1){
			$this->invite->deny();
			$im->removeInvite($player->getName(), $this->invite->getIsland()->getWorldName());
			$player->showModal(new IslandsUi($player, "Island invite has been denied!", false));
			return;
		}
		$player->showModal(new MyInvitesUi($player));
	}

}