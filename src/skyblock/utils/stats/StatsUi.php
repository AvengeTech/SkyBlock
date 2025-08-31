<?php namespace skyblock\utils\stats;

use pocketmine\player\Player;

use skyblock\{
    SkyBlockPlayer,
    SkyBlockSession
};

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class StatsUi extends SimpleForm{

	public function __construct(SkyBlockSession $gs){
		$combat = $gs->getCombat();
		$koth = $gs->getKoth();
		$crates = $gs->getCrates();

		parent::__construct(
			$gs->getUser()->getGamertag() . "'s stats",
			"General:" . PHP_EOL .
			TextFormat::ICON_TOKEN . " Techits: " . number_format($gs->getTechits()->getTechits()) . PHP_EOL .
			TextFormat::EMOJI_HOURGLASS_FULL . " Playtime: " . $gs->getPlaytime()->getFormattedPlaytime() . PHP_EOL . PHP_EOL . PHP_EOL .

			"Arena Stats:" . PHP_EOL .
			TextFormat::EMOJI_SKULL . " All time:" . PHP_EOL .
			"- Kills: " . number_format($kills = $combat->getKills(0)) . PHP_EOL .
			"- Deaths: " . number_format($deaths = $combat->getDeaths(0)) . PHP_EOL .
			"- KDR: " . ($deaths == 0 ? "N/A" : round($kills / $deaths, 2)) . PHP_EOL .
			"- Supply Drops: " . number_format($kills = $combat->getSupplyDrops(0)) . PHP_EOL .
			"- Money Bags: " . number_format($kills = $combat->getMoneyBags(0)) . PHP_EOL .
			"- Mobs: " . number_format($kills = $combat->getMobs(0)) . PHP_EOL . PHP_EOL .


			TextFormat::EMOJI_SKULL . " Monthly:" . PHP_EOL .
			"- Kills: " . number_format($kills = $combat->getKills(2)) . PHP_EOL .
			"- Deaths: " . number_format($deaths = $combat->getDeaths(2)) . PHP_EOL .
			"- KDR: " . ($deaths == 0 ? "N/A" : round($kills / $deaths, 2)) . PHP_EOL .
			"- Supply Drops: " . number_format($kills = $combat->getSupplyDrops(0)) . PHP_EOL .
			"- Money Bags: " . number_format($kills = $combat->getMoneyBags(0)) . PHP_EOL .
			"- Mobs: " . number_format($kills = $combat->getMobs(0)) . PHP_EOL . PHP_EOL .

			TextFormat::EMOJI_SKULL . " Weekly:" . PHP_EOL .
			"- Kills: " . number_format($kills = $combat->getKills(1)) . PHP_EOL .
			"- Deaths: " . number_format($deaths = $combat->getDeaths(1)) . PHP_EOL .
			"- KDR: " . ($deaths == 0 ? "N/A" : round($kills / $deaths, 2)) . PHP_EOL .
			"- Supply Drops: " . number_format($kills = $combat->getSupplyDrops(0)) . PHP_EOL .
			"- Money Bags: " . number_format($kills = $combat->getMoneyBags(0)) . PHP_EOL .
			"- Mobs: " . number_format($kills = $combat->getMobs(0)) . PHP_EOL . PHP_EOL .
			
			"KOTH Stats:" . PHP_EOL .
			TextFormat::EMOJI_SKULL . " All time:" . PHP_EOL .
			"- Kills: " . number_format($kills = $koth->getKills(0)) . PHP_EOL .
			"- Deaths: " . number_format($deaths = $koth->getDeaths(0)) . PHP_EOL .
			"- KDR: " . ($deaths == 0 ? "N/A" : round($kills / $deaths, 2)) . PHP_EOL .
			"- Wins: " . number_format($koth->getWins(0)) . PHP_EOL . PHP_EOL .

			TextFormat::EMOJI_SKULL . " Monthly:" . PHP_EOL .
			"- Kills: " . number_format($kills = $koth->getKills(2)) . PHP_EOL .
			"- Deaths: " . number_format($deaths = $koth->getDeaths(2)) . PHP_EOL .
			"- KDR: " . ($deaths == 0 ? "N/A" : round($kills / $deaths, 2)) . PHP_EOL .
			"- Wins: " . number_format($koth->getWins(2)) . PHP_EOL . PHP_EOL .

			TextFormat::EMOJI_SKULL . " Weekly:" . PHP_EOL .
			"- Kills: " . number_format($kills = $koth->getKills(1)) . PHP_EOL .
			"- Deaths: " . number_format($deaths = $koth->getDeaths(1)) . PHP_EOL .
			"- KDR: " . ($deaths == 0 ? "N/A" : round($kills / $deaths, 2)) . PHP_EOL .
			"- Wins: " . number_format($koth->getWins(1)) . PHP_EOL . PHP_EOL . PHP_EOL .

			"Crate Stats:" . PHP_EOL .
			"- Total opened: " . number_format($crates->getOpened()) . PHP_EOL .
			"- Iron keys: " . number_format($crates->getKeys("iron")) . PHP_EOL .
			"- Gold keys: " . number_format($crates->getKeys("gold")) . PHP_EOL .
			"- Diamond keys: " . number_format($crates->getKeys("diamond")) . PHP_EOL .
			"- Emerald keys: " . number_format($crates->getKeys("emerald")) . PHP_EOL .
			"- Divine keys: " . number_format($crates->getKeys("divine")) . PHP_EOL .
			"- Vote keys: " . number_format($crates->getKeys("vote")) . PHP_EOL
		);

		$this->addButton(new Button("Search"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$player->showModal(new StatSearchUi());
	}

}