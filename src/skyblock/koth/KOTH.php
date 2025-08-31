<?php

namespace skyblock\koth;

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
use skyblock\koth\commands\KothCommand;
use skyblock\koth\entity\CenterCrystal;

use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\utils\TextFormat as TF;
use pocketmine\Server;
use skyblock\koth\pieces\Game;

class KOTH{

	/** @var Game[] $games */
	private array $games = [];

	public function __construct(
		private SkyBlock $plugin
	){
		$plugin->getServer()->getCommandMap()->register("koth", new KothCommand($plugin, "koth", "King of the hill command"));

		EntityFactory::getInstance()->register(CenterCrystal::class, function(World $world, CompoundTag $nbt) : CenterCrystal{
			return new CenterCrystal(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ["minecraft:end_crystal", "CenterCrystal"]);

		$this->setupGames();
	}

	public function setupGames() : void{
		foreach(Structure::GAMES as $id => $data){
			$world = $data[Structure::DATA_WORLD];

			if(!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($world)){
				$this->plugin->getServer()->getWorldManager()->loadWorld($world, true);
			}
			$this->games[$id] = new Game(
				$id, 
				$data[Structure::DATA_NAME], 
				$world, 
				$data[Structure::DATA_TIME], 
				$data[Structure::DATA_CORNERS], 
				$this->setupPositions($data[Structure::DATA_SPAWNS]), 
				new Vector3(...$data[Structure::DATA_CENTER]), 
				$data[Structure::DATA_DISTANCE], 
				($data[Structure::DATA_GLASS] ?? [])
			);
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

	/** @return Game[] */
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

	/** @return Game[] */
	public function getActiveGames() : array{
		$games = [];
		foreach($this->getGames() as $id => $game){
			if($game->isActive()) $games[$id] = $game;
		}
		return $games;
	}

	public function inGame(Player $player) : bool{
		/** @var SkyBlockPlayer $player */
		return ($player->getGameSession()?->getKoth()->inGame() ?? false);
	}

	public function getGameByPlayer(Player $player) : ?Game{
		/** @var SkyBlockPlayer $player */
		return ($player->getGameSession()?->getKoth()->getGame() ?? null);
	}

	public function startKoth(string $name = "", bool $alert = true) : bool{
		$game = ($name === "" ? $this->getRandomGame() : $this->getGameById($name));
		if($game === null){
			return false;
		}

		if($game->isActive()){
			return false;
		}

		$types = [Game::TYPE_FULL, Game::TYPE_LIMITED];
		$type = $types[array_rand($types)];

		$game->setType($type);
		$game->setActive();

		if($alert){
			$kothType = match($type){
				Game::TYPE_FULL => "FULL CAPTURE",
				Game::TYPE_LIMITED => "LIMITED CAPTURE",
				default => "UNKNOWN CAPTURE"
			};

			Server::getInstance()->broadcastMessage($message = TF::GI . TF::LIGHT_PURPLE . "A " . TF::BOLD .  TF::DARK_PURPLE . $kothType . TF::RESET . TF::LIGHT_PURPLE ." KOTH event has started! " . TF::YELLOW . "/koth tp " . TF::AQUA . $game->getName());
		}

		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "koth",
			"data" => [
				"started" => true,
				"gameId" => $game->getIdentifier(),
				"typeId" => $game->getType(),
				"message" => $alert ? $message : ""
			]
		]))->queue();
		return true;
	}

	public function getHudFormat() : string{
		if(empty($this->getActiveGames())) return "";

		return TF::GRAY . "KOTH event started. " . TF::YELLOW . "/koth tp";
	}

}