<?php namespace skyblock\parkour;

use pocketmine\Server;
use pocketmine\world\Position;

use skyblock\SkyBlock;
use skyblock\parkour\command\ParkourCommand;
use skyblock\parkour\course\Course;

use core\Core;

class Parkour{
	
	public array $courses = [];

	public function __construct(public SkyBlock $plugin){
		if(!Core::thisServer()->isSubServer()){
			$plugin->getServer()->getCommandMap()->registerAll("skyblock", [
				new ParkourCommand($plugin, "parkour", "Teleport to parkour course!")
			]);
		}

		$this->setupCourses();
	}

	public function setupCourses() : void{
		foreach(Structure::COURSES as $id => $data){
			$world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
			if($world === null){
				Server::getInstance()->getWorldManager()->loadWorld($data["world"]);
				$world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
			}
			if($world === null) continue;
			$checkpoints = [];
			foreach($data["checkpoints"] as $checkpoint){
				$checkpoints[] = new Position($checkpoint[0], $checkpoint[1], $checkpoint[2], $world);
			}
			$this->courses[strtolower($data["name"])] = new Course(
				$id, $data["name"],
				$data["active"] ?? true,
				new Position($data["beginning"][0], $data["beginning"][1], $data["beginning"][2], $world),
				new Position($data["start"][0], $data["start"][1], $data["start"][2], $world),
				$checkpoints,
				new Position($data["end"][0], $data["end"][1], $data["end"][2], $world)
			);
		}
	}
	
	public function getCourses() : array{
		return $this->courses;
	}
	
	public function getCourse(string $name) : ?Course{
		return $this->courses[strtolower($name)] ?? null;
	}

}