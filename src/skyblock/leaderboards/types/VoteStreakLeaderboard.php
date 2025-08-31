<?php namespace skyblock\leaderboards\types;

use core\Core;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class VoteStreakLeaderboard extends Leaderboard implements MysqlUpdate{

	public function getType() : string{
		return "vote_streak";
	}

	public function calculate() : void{
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("update_leaderboard_" . $this->getType(), new MySqlQuery(
			"main",
			"SELECT xuid, highest FROM vote_streak ORDER BY highest DESC LIMIT " . $this->getSize() . ";",
			[]
		)), function(MySqlRequest $request) : void{
			$rows = $request->getQuery()->getResult()->getRows();
			$xuids = [];
			foreach($rows as $row){
				$xuids[] = $row["xuid"];
			}
			Core::getInstance()->getUserPool()->useUsers($xuids, function(array $users) use($rows) : void{
				$texts = [TextFormat::AQUA . TextFormat::BOLD . TextFormat::ICON_MINECOIN . " Highest Vote Streak " . TextFormat::ICON_MINECOIN];
				$i = 1;
				foreach($rows as $row){
					$texts[($gt = $users[$row["xuid"]]->getGamertag())] =
						TextFormat::RED . $i . ". " .
						TextFormat::YELLOW . $gt . " " . TextFormat::GRAY . "- " .
						TextFormat::AQUA . number_format($row["highest"]);
					$i++;
				}
				$this->texts = $texts;
				$this->updateSpawnedTo();
			});
		});
	}

}