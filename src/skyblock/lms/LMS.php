<?php namespace skyblock\lms;

use pocketmine\Server;
use pocketmine\entity\{
	EntityDataHelper,
	EntityFactory
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\lms\commands\LmsCommand;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\utils\TextFormat;

class LMS{

	public array $games = [];

	public function __construct(public SkyBlock $plugin){
		$plugin->getServer()->getCommandMap()->register("lms", new LmsCommand($plugin, "lms", "Last man standing command"));

		$this->setupGames();
	}

	public function setupGames() : void{
		foreach(Structure::GAMES as $id => $data){
			$level = $data["level"];
			if(!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($level)){
				$this->plugin->getServer()->getWorldManager()->loadWorld($level, true);
			}
			$this->games[$id] = new Game($id, $data["name"], $level, $data["time"], $data["corners"], $this->setupPositions($data["spawnpoints"]));
		}
		if(!Core::thisServer()->isSubServer() && SkyBlock::pvpOnMain()){
			foreach($this->getGames() as $game){
				$game->end(true);
			}
		}
	}

	public function setupPositions(array $positions) : array{
		foreach($positions as $key => $array){
			$positions[$key] = new Vector3(...$array);
		}
		return $positions;
	}

	public function tick() : void{
		foreach($this->getActiveGames() as $id => $game){
			$game->tick();
		}
	}

	public function close() : void{
		if(!Core::thisServer()->isSubServer()){
			foreach($this->getActiveGames() as $game){
				$game->end(true);
			}
		}
	}

	public function getGames() : array{
		return $this->games;
	}

	public function getRandomGame() : Game{
		return $this->games[array_rand($this->games)];
	}

	public function getGameById(string $id) : ?Game{
		return $this->games[$id] ?? null;
	}

	public function getGameByName(string $name) : ?Game{
		foreach($this->getGames() as $id => $game){
			if(strtolower($game->getName()) == strtolower($name)){
				return $game;
			}
		}
		return null;
	}

	public function getActiveGames() : array{
		$games = [];
		foreach($this->getGames() as $id => $game){
			if($game->isActive()) $games[$id] = $game;
		}
		return $games;
	}

	public function inGame(Player $player) : bool{
		/** @var SkyBlockPlayer $player */
		return $player->getGameSession()?->getLms()->inGame() ?? false;
	}

	public function getGameByPlayer(Player $player) : ?Game{
		/** @var SkyBlockPlayer $player */
		return $player->getGameSession()?->getLms()->getGame() ?? null;
	}

	public function startLms(string $name = "", bool $alert = true) : bool{
		$game = ($name === "" ? $this->getRandomGame() : $this->getGameById($name));
		if($game === null){
			return false;
		}

		if($game->isActive()){
			return false;
		}

		$game->setActive();

		if($alert){
			Server::getInstance()->broadcastMessage($message = TextFormat::GI . TextFormat::LIGHT_PURPLE . "A LMS event has started! " . TextFormat::YELLOW . "/lms tp " . TextFormat::AQUA . $game->getName());
		}

		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "lms",
			"data" => [
				"started" => true,
				"gameId" => $game->getId(),
				"message" => $alert ? $message : ""
			]
		]))->queue();
		return true;
	}

	public function getHudFormat() : string{
		if(empty($this->getActiveGames())) return "";

		return TextFormat::GRAY . "LMS event started. " . TextFormat::YELLOW . "/lms tp";
	}

}