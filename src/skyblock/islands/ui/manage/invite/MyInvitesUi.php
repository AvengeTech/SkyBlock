<?php namespace skyblock\islands\ui\manage\invite;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\ui\IslandsUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class MyInvitesUi extends SimpleForm{

	public array $invites = [];

	public function __construct(Player $player, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct("Island invites", ($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Tap an invite to view it's details!");
		foreach(SkyBlock::getInstance()->getIslands()->getInviteManager()->getInvitesFor($player) as $invite){
			$this->invites[] = $invite;
			$this->addButton(new Button($invite->getIsland()->getName() . PHP_EOL . TextFormat::RESET . TextFormat::DARK_GRAY . "[" . $invite->getIsland()->getPermissions()->getOwner()->getUser()->getGamertag() . "]"));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$invite = $this->invites[$response] ?? null;
		if($invite !== null){
			if(!($im = SkyBlock::getInstance()->getIslands()->getInviteManager())->hasInviteTo($player, $invite->getIsland())){
				$player->showModal(new MyInvitesUi($player, "This invite no longer exists!"));
				return;
			}
			$player->showModal(new ViewInviteUi($player, $invite));
			return;
		}
		$player->showModal(new IslandsUi($player));
	}

}