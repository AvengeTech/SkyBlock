<?php namespace skyblock\games\coinflips;

use pocketmine\player\Player;

use skyblock\{
	SkyBlock, 
	SkyBlockPlayer
};
use skyblock\games\coinflips\command\CoinflipCommand;

class Coinflips{

	public static int $gameId = 0;

	public array $games = [];

	public function __construct(SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->register("coinflip", new CoinflipCommand($plugin, "coinflip", "Flip a coin!"));
	}

	public function tick() : void{

	}

	public function close() : void{
		foreach($this->getGames() as $key => $game){
			$game->cancel();
			unset($this->games[$key]);
		}
	}

	public function getGames() : array{
		return $this->games;
	}
	
	public function getGame(int $id) : ?Coinflip{
		return $this->games[$id] ?? null;
	}
	
	public function newGame(Player $player, int $amount) : void{
		if(!($player instanceof SkyBlockPlayer)) return;

		$this->games[($id = self::$gameId++)] = new Coinflip($id, $player, $amount);
		$player->takeTechits($amount);
	}

	public function onQuit(Player $player) : void{
		foreach($this->getGames() as $key => $game){
			if($game->getPlayer() === $player){
				$game->cancel();
				unset($this->games[$key]);
			}else{
				echo "not yo game fool", PHP_EOL;
			}
		}
	}

}