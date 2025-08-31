<?php namespace skyblock\leaderboards\types;

use skyblock\SkyBlock;

use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class TopIslandLevelLeaderboard extends Leaderboard implements MysqlUpdate{

	public function getType() : string{
		return "top_island_level";
	}

	public function calculate() : void{
		SkyBlock::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("update_leaderboard_" . $this->getType(), new MySqlQuery(
			"main",
			"SELECT world, sizelevel FROM islands ORDER BY sizelevel DESC LIMIT " . $this->getSize() . ";",
			[]
		)), function(MySqlRequest $request) : void{
			$rows = $request->getQuery()->getResult()->getRows();
			$islands = [];
			foreach($rows as $row){
				$islands[] = $row["world"];
			}
			SkyBlock::getInstance()->getIslands()->getIslandManager()->loadIslands($islands, function(array $islands) use($rows) : void{
				$texts = [TextFormat::RED . TextFormat::BOLD . TextFormat::ICON_AVENGETECH . " Highest leveled islands " . TextFormat::ICON_AVENGETECH];
				$i = 1;
				foreach($rows as $row){
					$gt = ($gt = ($island = $islands[$row["world"]])->getPermissions()->getOwner()->getUser()->getGamertag());
					$texts[] =
						TextFormat::RED . $i . ". " .
						TextFormat::YELLOW . $gt . " " . TextFormat::GRAY . "[" . TextFormat::AQUA . $island->getName() . TextFormat::RESET . TextFormat::GRAY . "] - " .
						TextFormat::AQUA . number_format($row["sizelevel"]);
					$i++;
				}
				$this->texts = $texts;
				$this->updateSpawnedTo();
			});
		});
	}

}