<?php namespace skyblock\hud;

use pocketmine\Server;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};
use skyblock\settings\SkyBlockSettings;

use core\utils\TextFormat;

class HudObject{

	public string $name = "";
	public string $text = "";

	public int $color = 5;

	public function __construct(Player $player){
		$this->name = $player->getName();
		$this->text = "{MLVL}" . PHP_EOL . PHP_EOL . "{KOTH}";
	}

	public function getName() : string{
		return $this->name;
	}

	public function getPlayer() : ?Player{
		return Server::getInstance()->getPlayerExact($this->getName());
	}

	public function getPercentage() : float{
		/** @var SkyBlockPlayer $player */
		$player = $this->getPlayer();

		if(!$player->isLoaded()) return 1;

		$techits = $player->getTechits();
		$isession = $player->getGameSession()->getIslands();
		if(!$isession->atIsland()) return 1;

		$island = $isession->getIslandAt();
		if($island === null) return 1;
		$price = $island->getLevelUpPrice();

		return (($total = $techits / $price) > 1 ? 1 : $total);
	}

	public function getText() : string{
		/** @var SkyBlockPlayer $player */
		$player = $this->getPlayer();
		$text = $this->text;

		if(!$player->isLoaded()) return "";
		$isession = $player->getGameSession()->getIslands();

		$text = str_replace("{MLVL}", TextFormat::AQUA . number_format($player->getTechits()), $text);
		$text = str_replace("{KOTH}", SkyBlock::getInstance()->getKoth()->getHudFormat(), $text);

		return $text;
	}

	public function send() : void{
		$player = $this->getPlayer();

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_SHOW;
		$pk->healthPercent = $this->getPercentage();
		$pk->title = $this->getText();
		$pk->filteredTitle = $this->getText();
		$pk->darkenScreen = false;
		$pk->overlay = 0;
		$pk->color = 5;
		
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public function update() : void{
		/** @var SkyBlockPlayer $player */
		$player = $this->getPlayer();

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_TITLE;
		$pk->title = $this->getText();
		$pk->filteredTitle = $this->getText();
		$player->getNetworkSession()->sendDataPacket($pk);

		$pk = new BossEventPacket();
		$pk->bossActorUniqueId = $player->getId();
		$pk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
		$pk->healthPercent = $percent = $this->getPercentage();
		$player->getNetworkSession()->sendDataPacket($pk);

		if(!$player->isLoaded()) return;
		if($player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::RAINBOW_BOSS_BAR)){
			$pk = new BossEventPacket();
			$pk->bossActorUniqueId = $player->getId();
			$pk->eventType = BossEventPacket::TYPE_TEXTURE;
			$pk->darkenScreen = false;
			$pk->overlay = 0;
			$pk->color = ($percent != 1 ? 5 : (++$this->color > 6 ? $this->color = 0 : $this->color));
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

}