<?php namespace skyblock\islands\ui\manage;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

use skyblock\islands\{
	Island,
};
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\manage\permission\{
	EditPermissionsUi,
	EditVisitorPermissionsUi
};

use core\Core;
use core\session\CoreSession;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\BlockRegistry;
use core\utils\TextFormat;
use skyblock\generators\tile\OreGenerator;

class IslandManageUi extends SimpleForm{

	public function __construct(Player $player, public Island $island, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct(
			"Manage island",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .

			"Level up requirements:" . PHP_EOL .
			" - " . number_format($player->getTechits()) . "/" . number_format($island->getLevelUpPrice()) . " techits" . PHP_EOL .
			" - " . ($cm = $island->getChallengeManager())->getTotalChallengesCompleted() . "/" . $cm->getChallengesNeededToLevelUp() . " challenges completed" . PHP_EOL . PHP_EOL .
			
			"Select an option below to manage your island!"
		);

		$this->addButton(new Button("Level up island"));
		$this->addButton(new Button("Edit name"));
		$this->addButton(new Button("Edit description"));
		$this->addButton(new Button("Make island " . ($island->isPublic() ? "private" : "public")));
		$this->addButton(new Button(TextFormat::ICON_ENDERMITE . " Set time"));
		$this->addButton(new Button("Edit visitor permissions"));
		$this->addButton(new Button("Edit invite permissions"));
		
		$this->addButton(new Button(TextFormat::RED . "DELETE ISLAND"));

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = SkyBlock::getInstance()->getIslands()->getIslandManager()->getIslandBy($this->island->getWorldName());
		if($island === null){
			$player->sendMessage(TextFormat::RI . "Island is no longer loaded.");
			return;
		}
		$pp = $island->getPermissions()->getPermissionsBy($player);
		if($pp === null){
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit this island's permissions!");
			return;
		}
		if(!$pp->getPermission(Permissions::EDIT_ISLAND)){
			$player->showModal(new IslandInfoUi($player, $island, false, "You don't have permission to edit this island!"));
			return;
		}

		if($response == 0){
			if($player->getTechits() < $island->getLevelUpPrice()){
				$player->showModal(new IslandManageUi($player, $island, "You do not have enough techits to level up this island"));
				return;
			}
			if(($cm = $island->getChallengeManager())->getTotalChallengesCompleted() < $cm->getChallengesNeededToLevelUp()){
				$player->showModal(new IslandManageUi($player, $island, "You have not completed enough challenges to level up this island"));
				return;
			}

			$count = 0;
			$size = $island->getSizeLevel();
			$nextSize = $size + 1;

			if($nextSize >= 5){
				$items = [];
				$getsGen2 = false;

				if($getsPetBox = ($nextSize % 5 === 0)){
					$items[] = BlockRegistry::PET_BOX()->addData(BlockRegistry::PET_BOX()->asItem());
					
					$count++;
				}

				if($getsGen = ($nextSize >= 15)){
					$items[] = BlockRegistry::ORE_GENERATOR()->addData(
						BlockRegistry::ORE_GENERATOR()->asItem(),
						OreGenerator::TYPE_ANCIENT_DEBRIS,
						1,
						0
					);

					$count++;
					
					if($getsGen2 = ($nextSize % 5 == 0)){
						$items[] = BlockRegistry::ORE_GENERATOR()->addData(
							BlockRegistry::ORE_GENERATOR()->asItem(),
							OreGenerator::TYPE_GILDED_OBSIDIAN,
							1,
							0
						);

						$count++;
					}
				}

				// just to check
				foreach($items as $item){
					if(!$player->getInventory()->canAddItem($item)){
						$player->showModal(new IslandManageUi($player, $island, "Please free " . ($count > 1 ? $count . " inventory spaces" : "an inventory space") . " before leveling up!"));
						return;
					}
				}

				// Adds the item
				foreach($items as $item) $player->getInventory()->addItem($item);

				if ($getsGen2) $player->getGameSession()->getCrates()->addKeys("divine", 1);
			}

			$island->levelUp($player);

			$message = "Your island is now level " . $island->getSizeLevel() . "!";

			if($count > 0){
				$message .= "\n " . TextFormat::AQUA ."You received the following rewards:";
				
				if($getsPetBox) $message .= "\n - 1 Pet Box";
				if($getsGen) $message .= "\n - 1 Ancient Debris Generator";
				if($getsGen2){
					$message .= "\n - 1 Gilded Obsidian Generator";
					$message .= "\n - 1 Divine Key";
				}
			}

			$player->showModal(new IslandManageUi($player, $island, $message, false));
			return;
		}
		if($response == 1){
			$player->showModal(new EditNameUi($island));
			return;
		}
		if($response == 2){
			$player->showModal(new EditDescriptionUi($island));
			return;
		}
		if($response == 3){
			$island->setPublic(!$island->isPublic());
			$player->showModal(new IslandManageUi($player, $island, "Island is now " . ($island->isPublic() ? "public" : "private") . "!", false));
			return;
		}
		if($response == 4){
			$owner = $island->getPermissions()->getOwner();
			if($owner->getUser()->getGamertag() !== $player->getName()){
				Core::getInstance()->getSessionManager()->useSession($owner->getUser(), function(CoreSession $session) use($player, $island) : void{
					if(!$player->isConnected()) return;
					if(!$session->getRank()->hasRank()){
						$player->showModal(new IslandManageUi($player, $island, "The owner of this island must have a rank to modify the island time!"));
						return;
					}
					$player->showModal(new SetTimeUi($island));
				});
			}else{
				if(!$player->hasRank()){
					$player->showModal(new IslandManageUi($player, $island, "You must have a rank to modify your island's time!"));
					return;
				}
				$player->showModal(new SetTimeUi($island));
			}
			return;
		}
		if($response == 5){
			if(!$pp->getPermission(Permissions::EDIT_DEFAULT_PERMISSIONS)){
				$player->showModal(new IslandManageUi($player, $island, "You do not have permission to edit this island's default permissions!"));
				return;
			}
			$player->showModal(new EditVisitorPermissionsUi($island));
			return;
		}
		if($response == 6){
			if(!$pp->getPermission(Permissions::EDIT_DEFAULT_PERMISSIONS)){
				$player->showModal(new IslandManageUi($player, $island, "You do not have permission to edit this island's default permissions!"));
				return;
			}
			$player->showModal(new EditPermissionsUi($island, $island->getPermissions()->getDefaultInvitePermissions()));
			return;
		}
		if($response == 7){
			if(!$pp->isOwner()){
				$player->showModal(new IslandManageUi($player, $island, "Only the owner of this island can delete it!"));
				return;
			}
			if($island->getSizeLevel() < 5){
				$player->showModal(new IslandManageUi($player, $island, "You cannot delete your island until it is at least level 5!"));
				return;
			}
			$player->showModal(new DeleteIslandUi($island));
			return;
		}
		
		$player->showModal(new IslandInfoUi($player, $island));
	}

}