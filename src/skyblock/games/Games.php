<?php namespace skyblock\games;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\games\chat\{
	ChatGame,
	ChatGameData
};
use skyblock\games\coinflips\Coinflips;
use skyblock\games\tictactoe\TicTacToe;
use skyblock\games\rps\Rps;

use core\Core;

class Games{
	
	public int $ticks = 0;
	public ?ChatGame $currentChatGame = null;

	public Coinflips $coinflips;
	public TicTacToe $tictactoe;
	public Rps $rps;
	
	public function __construct(public SkyBlock $plugin){
		$this->coinflips = new Coinflips($plugin);
		$this->tictactoe = new TicTacToe($plugin);
		$this->rps = new Rps();

		if(Core::thisServer()->isSubServer())
			$this->ticks = -1;
	}
	
	public function tick() : void{
		if($this->ticks === -1) return;
		$this->ticks++;
		if($this->ticks % ChatGame::INTERVAL === 0 && $this->getCurrentChatGame() === null){
			$this->newChatGame();
		}

		$this->getCoinflips()->tick();
		$this->getTicTacToe()->tick();
		$this->getRps()->tick();
	}
	
	public function close() : void{
		$this->getCoinflips()->close();
		$this->getTicTacToe()->close();
	}
	
	public function getCurrentChatGame() : ?ChatGame{
		return $this->currentChatGame;
	}
	
	public function newChatGame() : void{
		switch(mt_rand(0, 2)){
			case ChatGame::TYPE_UNSCRAMBLE:
				$word = ChatGameData::UNSCRAMBLE_WORDS[mt_rand(0, count(ChatGameData::UNSCRAMBLE_WORDS) - 1)];
				$chatgame = $this->currentChatGame = new ChatGame(ChatGame::TYPE_UNSCRAMBLE, "", $word);
				break;
			case ChatGame::TYPE_RIDDLE:
				$data = ChatGameData::RIDDLES[mt_rand(0, count(ChatGameData::RIDDLES) - 1)];
				$chatgame = $this->currentChatGame = new ChatGame(ChatGame::TYPE_RIDDLE, $data[0], $data[1]);

				break;
			case ChatGame::TYPE_EQUATION:
				$data = ChatGameData::EQUATIONS[mt_rand(0, count(ChatGameData::EQUATIONS) - 1)];

				$num1 = mt_rand(1,200);
				$num2 = mt_rand(1,200);
				$type = mt_rand(0, 1);
				switch($type){
					case 0: //addition
						$game = new ChatGame(ChatGame::TYPE_EQUATION, "$num1 + $num2", (string) ($num1 + $num2));
						break;
					case 1: //subtraction
						$game = new ChatGame(ChatGame::TYPE_EQUATION, "$num1 - $num2", (string) ($num1 - $num2));
						break;
				}
				$chatgame = $this->currentChatGame = $game;

				break;
		}
		$chatgame->send();
	}
	

	public function getCoinflips() : Coinflips{
		return $this->coinflips;
	}

	public function getTicTacToe() : TicTacToe{
		return $this->tictactoe;
	}

	public function getRps() : Rps{
		return $this->rps;
	}
	
	public function onQuit(Player $player) : void{
		$this->getCoinflips()->onQuit($player);
	}
	
}