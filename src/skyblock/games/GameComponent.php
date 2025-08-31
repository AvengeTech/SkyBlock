<?php namespace skyblock\games;

use pocketmine\player\Player;

use skyblock\games\{
	rps\RpsGame,
	tictactoe\TicTacToeGame
};
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\component\BaseComponent;
use core\session\mysqli\data\MySqlQuery;

class GameComponent extends BaseComponent{

	public bool $rigged = false;

	public ?TicTacToeGame $tttGame = null;
	public ?RpsGame $rpsGame = null;

	public function getName() : string{
		return "game";
	}

	public function isRigged() : bool{
		return $this->rigged;
	}

	public function setRigged(bool $rigged = true) : void{
		$this->rigged = $rigged;
	}

	public function getRpsGame() : ?RpsGame{
		return null; //$this->tpsGame;
	}

	public function inRpsGame() : bool{
		return $this->getRpsGame() !== null;
	}

	public function setRpsGame(?RpsGame $game = null) : void{
		$this->rpsGame = $game;
	}

	public function getTttGame() : ?TicTacToeGame{
		return $this->tttGame;
	}

	public function inTttGame() : bool{
		return $this->getTttGame() !== null;
	}

	public function setTttGame(?TicTacToeGame $game = null) : void{
		$this->tttGame = $game;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			//"CREATE TABLE IF NOT EXISTS techits(xuid BIGINT(16) NOT NULL UNIQUE, techits INT NOT NULL DEFAULT 0)",
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		//$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM techits WHERE xuid=?", [$this->getXuid()]));
		//$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		//parent::loadAsync();
		//$this->finishLoadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		//$result = $request->getQuery()->getResult();
		//$rows = $result->getRows();
		//if(count($rows) > 0){
		//	$data = array_shift($rows);
		//	$this->techits = $data["techits"];
		//}

		//parent::finishLoadAsync($request);
	}

	public function verifyChange() : bool{
		// $verify = $this->getChangeVerify();
		return false;//$this->getTechits() !== $verify["techits"];
	}

	public function saveAsync() : void{
		// if(!$this->hasChanged() || !$this->isLoaded()) return;

		///$this->setChangeVerify([
		///	"techits" => $this->getTechits(),
		///]);

		//$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "INSERT INTO techits(xuid, techits) VALUES(?, ?) ON DUPLICATE KEY UPDATE techits=VALUES(techits)", [$this->getXuid(), $this->getTechits()]));
		//$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		//parent::saveAsync();
	}

	public function save() : bool{
		// if(!$this->hasChanged() || !$this->isLoaded()) return false;

		// $xuid = $this->getXuid();
		//$techits = $this->getTechits();
//
		//$db = $this->getSession()->getSessionManager()->getDatabase();
		//$stmt = $db->prepare("INSERT INTO techits(xuid, techits) VALUES(?, ?) ON DUPLICATE KEY UPDATE techits=VALUES(techits)");
		//$stmt->bind_param("ii", $xuid, $techits);
		//$stmt->execute();
		//$stmt->close();

		return false;// parent::save();
	}

}