<?php namespace skyblock\parkour\command;

use core\AtPlayer;
use core\staff\anticheat\session\SessionManager;
use core\command\type\CoreCommand;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer as Player;

class ParkourCommand extends CoreCommand {

	public function __construct(public SkyBlock $plugin, $name, $description){
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	/**
	 * @param Player $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$ps = $sender->getGameSession()->getParkour();
		if($ps->hasCourseAttempt()){
			$isession = $sender->getGameSession()->getIslands();
			if($isession->atIsland()){
				$isession->setIslandAt(null);
			}
			$ksession = $sender->getGameSession()->getKoth();
			if($ksession->inGame()){
				$ksession->setGame();
			}
			$attempt = $ps->getCourseAttempt();
			$pos = $attempt->getLastCheckpoint() ?? $attempt->getCourse()->getBeginningPosition();
			$sender->teleport($pos);
			$sender->sendMessage(TextFormat::GI . "Teleported to last checkpoint!");
			return;
		}
		$parkour = SkyBlock::getInstance()->getParkour();
		if(count($parkour->getCourses()) === 1){
			$courses = $parkour->getCourses();
			$course = array_shift($courses);
		}else{
			$course = $parkour->getCourse(array_shift($args) ?? "");
			if($course === null){
				$sender->sendMessage(TextFormat::RI . "Invalid course name!");
				return;
			}
		}
		$isession = $sender->getGameSession()->getIslands();
		if($isession->atIsland()){
			$isession->setIslandAt(null);
		}

		$ksession = $sender->getGameSession()->getKoth();
		if($ksession->inGame()){
			$ksession->setGame();
		}
		
		$sender->teleport($course->getBeginningPosition());
		$sender->sendMessage(TextFormat::GI . "Teleported to parkour course!");
	}
}