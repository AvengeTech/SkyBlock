<?php

namespace skyblock\islands\challenge;

use pocketmine\{
	player\Player,
	Server
};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\Event;

use skyblock\SkyBlockPlayer;

use core\Core;
use core\utils\TextFormat;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use skyblock\crates\event\CrateEvent;
use skyblock\crates\event\KeyTransactionEvent;
use skyblock\enchantments\event\ApplyEnchantmentEvent;
use skyblock\enchantments\event\RefineEssenceEvent;
use skyblock\enchantments\event\RepairItemEvent;
use skyblock\event\AutoInventoryCollectEvent;
use skyblock\fishing\event\FishingEvent;
use skyblock\generators\event\GeneratorEvent;
use skyblock\islands\event\IslandEvent;
use skyblock\pets\event\PetEvent;
use skyblock\pets\event\UnlockPetBoxEvent;
use skyblock\shop\event\ShopEvent;
use skyblock\spawners\event\SpawnerKillEvent;
use skyblock\spawners\event\SpawnerUpgradeEvent;

use function Discord\contains;

abstract class Challenge{

	const DEFAULT_DIFFICULTY = 1;
	const DEFAULT_TECHITS = 1000;

	const DIFFICULTY_STRINGS = [
		1 => "Easy",
		2 => "Normal",
		3 => "Hard",
	];

	public function __construct(
		private int $id,

		private string $name,
		private string $class,

		private string $description,
		private int $unlocklevel,

		private int $techits = self::DEFAULT_TECHITS,
		private int $difficulty = self::DEFAULT_DIFFICULTY,

		public array $progress = []
	){
		$this->progress["completed"] = false;
	}

	public function getId() : int{ return $this->id; }

	public function getName() : string{ return $this->name; }

	public function getClassName() : string{ return $this->class; }

	public function getDescription() : string{ return $this->description; }

	public function getUnlockLevel() : int{ return $this->unlocklevel; }

	public function getTechits() : int{ return $this->techits; }

	public function getDifficulty() : int{ return $this->difficulty; }

	public function getDifficultyString() : string{ return self::DIFFICULTY_STRINGS[$this->getDifficulty()]; }

	public function onCompleted(Player $player) : void{
		/** @var SkyBlockPlayer $player */
		Server::getInstance()->broadcastMessage(TextFormat::GI . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " has completed the challenge " . TextFormat::AQUA . $this->getName());
		$this->setCompleted($player);
		$player->addTechits($this->getTechits());
		$island = $player->getGameSession()->getIslands()->getIslandAt();
		$island?->updateScoreboardLines(false, false, true);
		$island?->getChallengeManager()->save();
		Core::broadcastToast(
			TextFormat::EMOJI_TROPHY . TextFormat::YELLOW . " Challenge completed [" . $this->getName() . "]",
			"    Completed by " . TextFormat::YELLOW . $player->getName() . TextFormat::WHITE . " for " . TextFormat::ICON_TOKEN . " " . TextFormat::AQUA . number_format($this->getTechits()) . " techits",
			$island->getWorld()->getPlayers()
		);
	}

	public function getProgress() : array{ return $this->progress; }

	public function isCompleted() : bool{ return $this->progress["c"] ?? false; }

	public function getCompletedBy() : string{ return $this->progress["cb"] ?? "unknown"; }

	public function getCompletedWhen() : int{ return $this->progress["cw"] ?? -1; }

	public function getCompletedWhenFormatted() : string{ return date("m/d/y", $this->getCompletedWhen()); }

	public function setCompleted(?Player $player, bool $complete = true) : void{
		$this->progress["c"] = $complete;
		$this->progress["cb"] = $player?->getName() ?? "Server";
		$this->progress["cw"] = time();
	}

	public function setProgressViaNBT(CompoundTag $nbt) : bool{
		if($nbt->getInt("id", -1) === -1){
			return false;
		}
		$this->progress = json_decode($nbt->getString("progress", json_encode(["c" => false])), true);
		return true;
	}

	public function getProgressNBT() : CompoundTag{
		return CompoundTag::create()->setInt("id", $this->getId())->setString("progress", json_encode($this->getProgress()));
	}

	public function fixChallengeData() : void{
		$progress = [];

		foreach($this->progress as $key => $data){
			if(isset(ChallengeData::CHALLENGES[$this->unlocklevel][$this->id]["progress"][$key])) $progress[$key] = $data;
			if(in_array($key, ["c", "cb", "cw", "completed"])) $progress[$key] = $data;

			unset($this->progress[$key]);
		}

		foreach(ChallengeData::CHALLENGES[$this->unlocklevel][$this->id]["progress"] as $key => $data){
			if(isset($progress[$key])){
				$data = ($progress[$key]["needed"] !== $data["needed"] ? [$progress[$key]["progress"], $data["needed"]] : $progress[$key]);

				if($data["progress"] > $data["needed"]) $data["progress"] = $data["needed"];
			}

			$progress[$key] = $data;
		}

		$this->progress = $progress;
	}

	/**
	 * Returns whether the challenge progress has been affected.
	 */
	public function event(Event $event, Player $player): bool {
		if($event instanceof ShopEvent) return $this->onShopEvent($event, $player);
		if($event instanceof IslandEvent) return $this->onIslandEvent($event, $player);
		if($event instanceof RepairItemEvent) return $this->onRepairEvent($event, $player);
		if($event instanceof KeyTransactionEvent) return $this->onKeyEvent($event, $player);
		if($event instanceof SpawnerKillEvent || $event instanceof SpawnerUpgradeEvent) return $this->onSpawnerEvent($event, $player);
		if($event instanceof GeneratorEvent) return $this->onGeneratorEvent($event, $player);
		if($event instanceof FishingEvent) return $this->onFishEvent($event, $player);
		if($event instanceof CraftItemEvent) return $this->onCraftEvent($event, $player);
		if($event instanceof PlayerExperienceChangeEvent) return $this->onExperienceEvent($event, $player);
		if($event instanceof BlockPlaceEvent || $event instanceof BlockBreakEvent) return $this->onBlockEvent($event, $player);
		if($event instanceof PetEvent || $event instanceof UnlockPetBoxEvent) return $this->onPetEvent($event, $player);
		if($event instanceof EntityItemPickupEvent || $event instanceof AutoInventoryCollectEvent) return $this->onCollectEvent($event, $player);
		if($event instanceof PlayerInteractEvent) return $this->onInteractEvent($event, $player);
		if($event instanceof CrateEvent) return $this->onCrateEvent($event, $player);
		if($event instanceof RefineEssenceEvent) return $this->onEssenceEvent($event, $player);
		if($event instanceof ApplyEnchantmentEvent) return $this->onApplyEvent($event, $player);
		return $this->onEvent($event, $player);
	}

	public function onShopEvent(ShopEvent $event, Player $player) : bool{ return false; }

	public function onIslandEvent(IslandEvent $event, Player $player) : bool{ return false; }

	public function onRepairEvent(RepairItemEvent $event, Player $player) : bool{ return false; }

	public function onKeyEvent(KeyTransactionEvent $event, Player $player) : bool{ return false; }

	public function onSpawnerEvent(SpawnerKillEvent|SpawnerUpgradeEvent $event, Player $player) : bool{ return false; }

	public function onGeneratorEvent(GeneratorEvent $event, Player $player) : bool{ return false; }

	public function onApplyEvent(ApplyEnchantmentEvent $event, Player $player) : bool{ return false; }

	public function onFishEvent(FishingEvent $event, Player $player) : bool{ return false; }

	public function onEssenceEvent(RefineEssenceEvent $event, Player $player) : bool{ return false; }

	public function onCraftEvent(CraftItemEvent $event, Player $player) : bool{ return false; }

	public function onExperienceEvent(PlayerExperienceChangeEvent $event, Player $player) : bool{ return false; }

	public function onBlockEvent(BlockPlaceEvent|BlockBreakEvent $event, Player $player) : bool{ return false; }

	public function onCollectEvent(EntityItemPickupEvent|AutoInventoryCollectEvent $event, Player $player) : bool{ return false; }

	public function onInteractEvent(PlayerInteractEvent $event, Player $player) : bool{ return false; }

	public function onCrateEvent(CrateEvent $event, Player $player) : bool{ return false; }

	public function onPetEvent(PetEvent|UnlockPetBoxEvent $event, Player $player) : bool{ return false; }

	public function onEvent(PlayerEvent $event, Player $player) : bool{ return false; }
}
