<?php namespace skyblock\techits\commands;

use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

use skyblock\{
	SkyBlock,
	SkyBlockPlayer as Player
};

class TopTechits extends CoreCommand{

	public function __construct(public SkyBlock $plugin, string $name, string $description){
		parent::__construct($name, $description);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) : void{
		if(count($args) == 0){
			$page = 1;
			$type = 0;
		}else{
			$page = (int) $args[0] ?? 1;
			$type = $args[1] ?? 0;
			if(in_array(strtolower($type), ["database", "db"])){
				$type = 1;
			}else{
				$type = (int) $type;
				if($type < 0 || $type > 1){
					$type = 0;
				}
			}
		}

		$page = min(100, $page);

		if($type == 1 && $sender instanceof Player && !$sender->isStaff()){
			$type = 0;
		}

		SkyBlock::getInstance()->getTechits()->getTop($page, 10, $type, function(array $top) use($sender, $type, $page) : void{
			$sender->sendMessage(TextFormat::GRAY . "Top techits (Page: " . TextFormat::YELLOW . $page . TextFormat::GRAY . ", Type: " . TextFormat::AQUA . ($type == 0 ? "Online" : "Database") . TextFormat::GRAY . ")");
			$i = ($page - 1) * 10 + 1;
			foreach($top as $name => $techits){
				$sender->sendMessage(TextFormat::YELLOW . $i . ". " . TextFormat::GREEN . $name . TextFormat::YELLOW . " - " . TextFormat::AQUA . number_format($techits) . " Techits");
				$i++;
			}
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}