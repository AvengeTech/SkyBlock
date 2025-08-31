<?php namespace skyblock\islands\ui\manage;

use pocketmine\Server;
use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\islands\Island;
use skyblock\islands\permission\Permissions;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class VisitorsUi extends SimpleForm{

	public bool $kick = false;

	public array $visitors = [];

	public function __construct(Player $player, public Island $island, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct(
			"Island visitors",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Here is a list of visitors currently on this island"
		);

		if($island->getWorld() !== null){
			$perms = $island->getPermissions()->getPermissionsBy($player);
			$ck = $this->kick = $perms !== null && $perms->getPermission(Permissions::KICK_VISITORS);

			foreach($island->getWorld()->getPlayers() as $pl){
				if($island->getPermissions()->getPermissionsBy($pl) === null){
					$this->addButton(new Button($pl->getName() . ($ck ? PHP_EOL . TextFormat::EMOJI_X . " Tap to kick" : "")));
					$this->visitors[] = $pl->getName();
				}
			}
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$visitor = $this->visitors[$response] ?? null;
		if($visitor !== null && $this->kick){
			$pl = Server::getInstance()->getPlayerExact($visitor);
			if(
				$pl === null ||
				$pl->getPosition()->getWorld()->getDisplayName() !== $this->island->getWorldName()
			){
				$player->showModal(new VisitorsUi($player, $this->island, "This player is no longer on the island!"));
				return;
			}
			$this->island->kick($pl);
			$player->showModal(new VisitorsUi($player, $this->island, $pl->getName() . " has been kicked!", false));
			return;
		}
		$player->showModal(new IslandInfoUi($player, $this->island));
	}

}