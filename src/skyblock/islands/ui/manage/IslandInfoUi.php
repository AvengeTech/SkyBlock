<?php namespace skyblock\islands\ui\manage;

use pocketmine\player\Player;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\islands\{
	Island,
};
use skyblock\islands\challenge\ui\ChallengeUi;
use skyblock\islands\entity\IslandEntity;
use skyblock\islands\permission\Permissions;
use skyblock\islands\ui\IslandsUi;
use skyblock\islands\ui\access\PublicIslandsUi;
use skyblock\islands\ui\manage\permission\block\BlockListUi;
use skyblock\islands\warp\ui\IslandWarpsUi;
use skyblock\shop\ui\ShopUi;

use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class IslandInfoUi extends SimpleForm{

	public function __construct(Player $player, public Island $island, public bool $teleport = false, string $message = "", bool $error = true) {
		/** @var SkyBlockPlayer $player */
		parent::__construct(
			"Island information",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .

			"Name: " . $island->getName() . TextFormat::RESET . PHP_EOL .
			"Description: " . $island->getDescription() . TextFormat::RESET . PHP_EOL .
			"Level: " . $island->getSizeLevel() . PHP_EOL . PHP_EOL .

			"Warps: " . count(($wm = $island->getWarpManager())->getWarps()) . "/" . $wm->getWarpLimit() . PHP_EOL . PHP_EOL .

			"Gen blocks: " . $island->getGenCount() . "/" . $island->getMaxGenCount() . PHP_EOL .
			"Spawners: " . $island->getSpawnerCount() . "/" . $island->getMaxSpawnerCount() . PHP_EOL .
			"Hoppers: " . $island->getHopperCount() . "/" . $island->getMaxHopperCount() . PHP_EOL . PHP_EOL .

			"Date created: " . $island->getCreatedFormatted() . PHP_EOL .
			"Type: " . SkyBlock::getInstance()->getIslands()->getIslandManager()->getGeneratorInfo($island->getIslandType())->getName() . PHP_EOL
		);

		if($teleport){
			$this->addButton(new Button("Teleport"));
			$this->addButton(new Button("Go back"));
		}else{
			$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

			if($permissions->getPermission(Permissions::OWNER) || $permissions->getPermission(Permissions::EDIT_ISLAND)){
				$this->addButton(new Button("Manage island"));
			}
			$cm = $island->getChallengeManager();
			$this->addButton(new Button("Challenges" . PHP_EOL . "(" . $cm->getTotalChallengesCompleted() . "/" . SkyBlock::getInstance()->getIslands()->getChallenges()->getChallengeCount() . " completed)"));
			if($permissions->getPermission(Permissions::USE_SHOP)){
				$this->addButton(new Button("Shop"));
			}
			$this->addButton(new Button("Warps"));

			$this->addButton(new Button("Members" . PHP_EOL . "(" . count($ip->getMembersOnIsland()) . "/" . count($ip->getPermissions()) . " online)"));
			$this->addButton(new Button("Visitors"));
			$this->addButton(new Button("Block list"));

			if($permissions->getPermission(Permissions::MOVE_ISLAND_MENU)){
				$this->addButton(new Button("Move island menu to" . PHP_EOL . "current position"));
			}
			if(!$permissions->isOwner() && $permissions->getUser()->getGamertag() !== "Default Visitor"){
				$this->addButton(new Button("Leave island"));
			}
		}
	}

	public function handle($response, Player $player){
		/** @var SkyBlockPlayer $player */
		$island = $this->island;
		if($this->teleport){
			if($response === 0){
				SkyBlock::getInstance()->getIslands()->getIslandManager()->gotoIsland($player, $island);
			}else{
				SkyBlock::getInstance()->getIslands()->getIslandManager()->getAllPublicIslands(function(array $islands) use($player) : void{
					if(!$player->isConnected()) return;
					if(count($islands) === 0){
						$player->showModal(new IslandsUi($player, "No public islands are currently available"));
						return;
					}
					$player->showModal(new PublicIslandsUi($player, $islands));
				});
			}
		}else{
			$permissions = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

			if($permissions->isOwner() || $permissions->getPermission(Permissions::EDIT_ISLAND)){
				if($response == 0){
					$player->showModal(new IslandManageUi($player, $island));
					return;
				}
				$response--;
			}

			if($response == 0){
				$player->showModal(new ChallengeUi($player, true));
				return;
			}
			if($permissions->getPermission(Permissions::USE_SHOP)){
				if($response == 1){
					$player->showModal(new ShopUi($player, true));
					return;
				}
				$response--;
			}
			if($response == 1){
				if(!$permissions->getPermission(Permissions::USE_WARPS)){
					$player->showModal(new IslandInfoUi($player, $island, false, "You do not have access to this island's warps"));
					return;
				}
				$player->showModal(new IslandWarpsUi($player, $island));
				return;
			}
			if($response == 2){
				$player->showModal(new IslandMembersUi($player, $island));
				return;
			}
			if($response == 3){
				$player->showModal(new VisitorsUi($player, $island));
				return;
			}
			if($response == 4){
				$player->showModal(new BlockListUi($player, $island));
				return;
			}
			if($response == 5){
				if($permissions->getPermission(Permissions::MOVE_ISLAND_MENU)){
					if(($ie = $island->getIslandEntity()) === null || $ie->isClosed() || $ie->isFlaggedForDespawn()){
						$ie = new IslandEntity($player->getLocation());
						$ie->spawnToAll();
					}else{
						$ie->teleport($player->getPosition());
					}
					$player->showModal(new IslandInfoUi($player, $island, false, "Island menu has been moved!", false));
					return;
				}
				if(!$permissions->isOwner()){
					$player->showModal(new LeaveIslandUi($island));
					return;
				}
			}
		}
	}

}