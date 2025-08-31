<?php namespace skyblock\leaderboards\types;

use skyblock\SkyBlock;

use core\Core;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class ParkourAllTimeCompletionsLeaderboard extends Leaderboard implements MysqlUpdate{

	public function __construct(public string $courseName, int $size = 10){
		parent::__construct($size);
	}

	public function getCourseName() : string{
		return $this->courseName;
	}

	public function getType() : string{
		return "parkour_alltime_completions_" . $this->getCourseName();
	}

	public function calculate() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("update_leaderboard_" . $this->getType(), new MySqlQuery(
			"main",
			"SELECT xuid, total FROM parkour_times WHERE course='" . $this->getCourseName() . "' ORDER BY total DESC LIMIT " . $this->getSize() . ";",
			[]
		)), function(MySqlRequest $request) : void{
			$rows = $request->getQuery()->getResult()->getRows();
			$xuids = [];
			foreach($rows as $row){
				$xuids[] = $row["xuid"];
			}
			Core::getInstance()->getUserPool()->useUsers($xuids, function(array $users) use($rows) : void{
				$texts = [TextFormat::GREEN . TextFormat::BOLD . TextFormat::EMOJI_TROPHY . " " . ucfirst($this->getCourseName()) . " Completions " . TextFormat::EMOJI_TROPHY];
				$i = 1;
				foreach($rows as $row){
					$texts[($gt = $users[$row["xuid"]]->getGamertag())] =
						TextFormat::RED . $i . ". " .
						TextFormat::YELLOW . $gt . " " . TextFormat::GRAY . "- " .
						TextFormat::AQUA . number_format($row["total"]);
					$i++;
				}
				$this->texts = $texts;
				$this->updateSpawnedTo();
			});
		});
	}

}