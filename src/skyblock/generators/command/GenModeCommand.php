<?php namespace skyblock\generators\command;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use skyblock\generators\tile\OreGenerator;
use skyblock\SkyBlock;
use skyblock\islands\permission\Permissions;
use skyblock\SkyBlockPlayer as Player;

class GenModeCommand extends CoreCommand {

	public function __construct(
		private SkyBlock $plugin, 
		string $name, 
		string $description
	){
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["gm"]);
	}

	/**
	 * @param SkyBlockPlayer $sender
	 */
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$isession = $sender->getGameSession()->getIslands();
		if(!$isession->atIsland()){
			$sender->sendMessage(TextFormat::RI . "You must be at an island to use this command!");
			return;
		}
		$island = $isession->getIslandAt();
		$perm = $island->getPermissions()->getPermissionsBy($sender) ?? $island->getPermissions()->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::EDIT_GEN_BLOCKS)){
			$sender->sendMessage(TextFormat::RI . "You don't have permission to edit generator blocks on this island");
			return;
		}
		if($sender->isTier3() && count($args) > 1){
			$type = array_shift($args);
			switch($type){
				case "give":
					$block = array_shift($args);
					
					switch($block){
						case "am":
							$hExtender = (int) (count($args) == 0 ? 0 : array_shift($args));
							$vExtender = (int) (count($args) == 0 ? 0 : array_shift($args));

							$og = BlockRegistry::AUTOMINER()->addData(
								BlockRegistry::AUTOMINER()->asItem(),
								[OreGenerator::DATA_HORIZONTAL => $hExtender, OreGenerator::DATA_VERTICAL => $vExtender]
							);
							$sender->getInventory()->addItem($og);
							$sender->sendMessage(TextFormat::GI . "You were given an autominer!");
							break;
						case "db":
							$level = (($level = (int) array_shift($args)) === 0 ? 1 : $level);
							$boost = (int) array_shift($args);

							$og = BlockRegistry::DIMENSIONAL_BLOCK();
							$item = $og->asItem();
							$og->addData($level, $boost, $item);
							$sender->getInventory()->addItem($item);
							$sender->sendMessage(TextFormat::GI . "You were given a dimensional block!");
							break;
						case "og":
							$type = (int) array_shift($args);
							$level = (int) (count($args) == 0 ? 1 : array_shift($args));
							$boost = (int) (count($args) == 0 ? 0 : array_shift($args));
							$hExtender = (int) (count($args) == 0 ? 0 : array_shift($args));
							$vExtender = (int) (count($args) == 0 ? 0 : array_shift($args));
							$solidifierLevel = (int) (count($args) == 0 ? 0 : array_shift($args));
							$solidifierRuns = (int) (count($args) == 0 ? 0 : array_shift($args));

							$og = BlockRegistry::ORE_GENERATOR()->addData(
								BlockRegistry::ORE_GENERATOR()->asItem(),
								$type,
								$level,
								$boost,
								[OreGenerator::DATA_HORIZONTAL => $hExtender, OreGenerator::DATA_VERTICAL => $vExtender],
								[OreGenerator::DATA_LEVEL => $solidifierLevel, OreGenerator::DATA_RUNS => $solidifierRuns]
							);

							$sender->getInventory()->addItem($og);
							$sender->sendMessage(TextFormat::GI . "You were given an ore generator!");
							break;
						case "gb":
							$value = (int) array_shift($args);

							$item = ItemRegistry::GEN_BOOSTER();
							$item->setup($value);
							$sender->getInventory()->addItem($item);
							$sender->sendMessage(TextFormat::GI . "You were given a gen booster!");
							break;
					}
					return;
			}
			
		}

		if($sender->toggleGenMode()){
			$sender->sendMessage(TextFormat::GI . "Gen mode has been enabled! You can now place/break/modify generator blocks! (Ore generators, autominers, dimensional blocks)");
		}else{
			$sender->sendMessage(TextFormat::GI . "Gen mode has been disabled!");
		}
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}