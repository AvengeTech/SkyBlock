<?php namespace skyblock\islands\ui\help;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class CommandInfoUi extends SimpleForm{

	public function __construct(string $name, array $entry){
		parent::__construct("Subcommand - " . $name,
			"Command usage: /island " . $name . implode(" ", ($entry["arguments"] ?? [])) . PHP_EOL .
			"Aliases: " . implode(", ", ($entry["alias"] ?? [])) . PHP_EOL . PHP_EOL .

			"Description: " . $entry["description"]
		);
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$player->showModal(new CommandHelpUi($player));
	}

}