<?php namespace skyblock\leaderboards\types;

use skyblock\SkyBlock;
use skyblock\koth\KothStat;

use core\Core;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class KothWinsLeaderboard extends Leaderboard implements MysqlUpdate{

	public function __construct(public int $type, int $size = 5){
		parent::__construct($size);
	}

	public function getStatType() : int{
		return $this->type;
	}

	public function typeToName() : string{
		return match($this->getStatType()){
			KothStat::TYPE_ALLTIME => "All time",
			KothStat::TYPE_WEEKLY => "Weekly",
			KothStat::TYPE_MONTHLY => "Monthly",
			default => ""
		};
	}
	
	public function getType() : string{
		return "koth_alltime_wins";
	}

	public function calculate() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("update_leaderboard_" . $this->getType() . "_" . $this->typeToName(), new MySqlQuery(
			"main",
			"SELECT xuid, wins FROM koth_stats WHERE ttype=" . $this->getStatType() . " ORDER BY wins DESC LIMIT " . $this->getSize() . ";",
			[]
		)), function(MySqlRequest $request) : void{
			$rows = $request->getQuery()->getResult()->getRows();
			$xuids = [];
			foreach($rows as $row){
				$xuids[] = $row["xuid"];
			}
			Core::getInstance()->getUserPool()->useUsers($xuids, function(array $users) use($rows) : void{
				$texts = [TextFormat::RED . TextFormat::BOLD . TextFormat::EMOJI_TROPHY . " KOTH wins (" . $this->typeToName() . ") " . TextFormat::EMOJI_TROPHY];
				$i = 1;
				foreach($rows as $row){
					$texts[($gt = $users[$row["xuid"]]->getGamertag())] =
						TextFormat::RED . $i . ". " .
						TextFormat::YELLOW . $gt . " " . TextFormat::GRAY . "- " .
						TextFormat::AQUA . number_format($row["wins"]);
					$i++;
				}
				$this->texts = $texts;
				$this->updateSpawnedTo();
			});
		});
	}

}