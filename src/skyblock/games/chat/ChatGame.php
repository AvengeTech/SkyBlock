<?php namespace skyblock\games\chat;

use pocketmine\Server;
use pocketmine\player\Player;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer
};

use core\utils\TextFormat;

class ChatGame{

	const INTERVAL = 10 * 60;

	const TYPE_UNSCRAMBLE = 0; //only has answer
	const TYPE_RIDDLE = 1;
	const TYPE_EQUATION = 2;

	const DIFFICULTY_EASY = 0;
	const DIFFICULTY_INTERMEDIATE = 0;
	const DIFFICULTY_HARD = 0;

	public string $extra = "";

	public function __construct(
		public int $type,
		public string $question,
		public string $answer,
		public int $difficulty = self::DIFFICULTY_EASY
	){}

	public function getType() : int{
		return $this->type;
	}

	public function getQuestion() : string{
		return $this->question;
	}

	public function getAnswer() : string{
		return $this->answer;
	}

	public function getDifficulty() : int{
		return $this->difficulty;
	}

	public function getPrize() : int{
		switch($this->getDifficulty()){
			case self::DIFFICULTY_EASY:
				return 1000;
				break;
			case self::DIFFICULTY_INTERMEDIATE:
				return 10000;
				break;
			case self::DIFFICULTY_HARD:
				return 100000;
				break;
		}
	}

	public function getScrambledAnswer() : string{
		if($this->extra !== "") return $this->extra;
		return $this->extra = str_shuffle($this->getAnswer());
	}

	public function getQuestionDisplay() : string{
		switch($this->getType()){
			case self::TYPE_UNSCRAMBLE:
				$q = "Unscramble the word for " . TextFormat::AQUA . number_format($this->getPrize()) . " techits" . TextFormat::GRAY . ": " . TextFormat::YELLOW . $this->getScrambledAnswer();
				break;
			case self::TYPE_RIDDLE:
				$q = "Solve the following riddle for " . TextFormat::AQUA . number_format($this->getPrize()) . " techits" . TextFormat::GRAY . ": " . TextFormat::YELLOW . $this->getQuestion();
				break;
			case self::TYPE_EQUATION:
				$q = "Solve the following equation for " . TextFormat::AQUA . number_format($this->getPrize()) . " techits" . TextFormat::GRAY . ": " . TextFormat::YELLOW . $this->getQuestion();
				break;
		}
		return TextFormat::YELLOW . TextFormat::BOLD . "[~] " . TextFormat::RESET . TextFormat::GRAY . $q;
	}

	public function send(?Player $player = null) : void{
		$msg = $this->getQuestionDisplay();
		if($player !== null){
			$player->sendMessage($msg);
			return;
		}
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$player->sendMessage($msg);
		}
	}

	public function winner(Player $winner) : void{
		/** @var SkyBlockPlayer $winner */
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			//$player->addSound()
			$player->sendMessage(TextFormat::YELLOW . TextFormat::BOLD . "[~] " . TextFormat::RESET . TextFormat::YELLOW . $winner->getName() . TextFormat::GRAY . " guessed the correct answer! (" . TextFormat::AQUA . $this->getAnswer() . TextFormat::GRAY . ")");
		}

		$winner->addTechits($this->getPrize());

		SkyBlock::getInstance()->getGames()->currentChatGame = null;
	}
	
}