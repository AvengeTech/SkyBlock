<?php namespace skyblock\hud;

use pocketmine\player\Player;

use skyblock\SkyBlock;

class Hud{

	public $plugin;

	public $huds = [];

	public function __construct(SkyBlock $plugin){
		$this->plugin = $plugin;
	}

	public function send(Player $player) : void{
		$this->huds[$player->getName()] = new HudObject($player);
		$this->huds[$player->getName()]->send();
	}

	public function tick() : void{
		foreach($this->huds as $name => $hud){
			$player = $this->plugin->getServer()->getPlayerExact($name);
			if($player instanceof Player && $player->isConnected()){
				$hud->update();
			}else{
				unset($this->huds[$name]);
			}
		}
	}

}