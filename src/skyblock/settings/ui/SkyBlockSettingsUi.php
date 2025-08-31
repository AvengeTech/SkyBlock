<?php namespace skyblock\settings\ui;

use pocketmine\player\Player;

use skyblock\SkyBlockPlayer;
use skyblock\settings\SkyBlockSettings;

use core\settings\ui\SettingsUi;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Toggle,
	Dropdown
};
use core\utils\TextFormat;

class SkyBlockSettingsUi extends CustomForm{
	
	public array $islands = [];

	public function __construct(Player $player, array $islands = []){
		parent::__construct("SkyBlock settings");

		/** @var SkyBlockPlayer $player */
		$settings = $player->getGameSession()->getSettings()->getSettings();
		$this->addElement(new Label("Free settings"));
		$this->addElement(new Toggle("Rainbow boss bar", $settings[SkyBlockSettings::RAINBOW_BOSS_BAR]));
		$this->addElement(new Toggle("No tool drop", $settings[SkyBlockSettings::NO_TOOL_DROP]));
		$this->addElement(new Toggle("Tool break alert", $settings[SkyBlockSettings::TOOL_BREAK_ALERT]));
		$this->addElement(new Toggle("Lightning strikes", $settings[SkyBlockSettings::LIGHTNING]));
		$this->addElement(new Toggle("Auto Inv", $settings[SkyBlockSettings::AUTO_INV]));
		$this->addElement(new Toggle("Auto XP", $settings[SkyBlockSettings::AUTO_XP]));
		$this->addElement(new Toggle("Island chat", $settings[SkyBlockSettings::ISLAND_CHAT] ?? false));

		$defIsland = $settings[SkyBlockSettings::DEFAULT_ISLAND] ?? "";
		$elements = [];
		$default = "";
		foreach($islands as $island){
			$elements[] = $island->getName();
			$this->islands[] = $island->getWorldName();
			if($island->getWorldName() == $defIsland){
				$default = $island->getName();
			}
		}
		$dropdown = new Dropdown("Default Island", $elements);
		$dropdown->setOptionAsDefault($default);
		$this->addElement($dropdown);
		
		$this->addElement(new Label("Ranked settings"));
		$this->addElement(new Toggle(TextFormat::ICON_BLAZE . " Auto enable island /fly", $settings[SkyBlockSettings::AUTO_ISLAND_FLIGHT] ?? false));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$session = $player->getGameSession()->getSettings();

		$session->setSetting(SkyBlockSettings::RAINBOW_BOSS_BAR, $response[1]);
		$session->setSetting(SkyBlockSettings::NO_TOOL_DROP, $response[2]);
		$session->setSetting(SkyBlockSettings::TOOL_BREAK_ALERT, $response[3]);
		$session->setSetting(SkyBlockSettings::LIGHTNING, $response[4]);
		$session->setSetting(SkyBlockSettings::AUTO_INV, $response[5]);
		$session->setSetting(SkyBlockSettings::AUTO_XP, $response[6]);
		$session->setSetting(SkyBlockSettings::ISLAND_CHAT, $response[7]);
		$session->setSetting(SkyBlockSettings::DEFAULT_ISLAND, $this->islands[$response[8]]);

		if($player->getRankHierarchy() >= $player->getRankHierarchy("blaze"))
			$session->setSetting(SkyBlockSettings::AUTO_ISLAND_FLIGHT, $response[10]);

		$player->showModal(new SettingsUi(TextFormat::EMOJI_CHECKMARK . TextFormat::GREEN . " SkyBlock settings have been updated!"));
	}

}