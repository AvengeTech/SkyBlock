<?php

namespace skyblock\spawners\block;

use pocketmine\block\{
	Block,
	MonsterSpawner,
};
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\BlockTransaction;

use skyblock\SkyBlock;
use skyblock\spawners\tile\Spawner;

use core\utils\TextFormat as TF;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\enchantments\Enchantments;
use skyblock\spawners\Spawners;

class MobSpawner extends MonsterSpawner{

	const SPAWNER_LEVEL = "SpawnerLevel";

	public function getXpDropAmount() : int { return 0; }

	public function getPickedItem(bool $addUserData = false) : Item{
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof Spawner){
			return $this->addData(
				$this->asItem(),
				$tile->getSpawnerLevel()
			);
		}

		return $this->asItem();
	}

	public function getDrops(Item $item) : array{
		if(($tile = $this->position->getWorld()->getTile($this->position)) instanceof Spawner){
			return [$this->addData(
				$this->asItem(),
				$tile->getSpawnerLevel()
			)];
		}

		return [$this->asItem()];
	}

	public function addData(Item $item, int $level) : Item{
		$item->setCustomName(TF::RESET . TF::RED . "Spawner " . TF::YELLOW . "LVL" . $level);
		$item->getNamedTag()->setInt(self::SPAWNER_LEVEL, $level);

		$lores = [];
		$lores[] = "This spawner is level " . TF::YELLOW . $level;
		$lores[] = "and will spawn " . TF::AQUA . Spawners::LEVEL_MOB_NAMES[$level] . "s" . TF::GRAY . ".";

		foreach($lores as $key => $lore){
			$lores[$key] = TF::RESET . TF::GRAY . $lore;
		}
		$item->setLore($lores);
		$item->addEnchantment(EnchantmentRegistry::OOF()->getEnchantmentInstance());
		return $item;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {
		if(parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player)) {
			if(($level = $item->getNamedTag()->getInt(self::SPAWNER_LEVEL, -1)) === -1) return false;

			SkyBlock::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($level, $blockReplace) : void{
				$tile = $this->getPosition()->getWorld()->getTile($blockReplace->getPosition());

				if(!is_null($tile) && $tile instanceof Spawner) $tile->setSpawnerLevel($level);
			}), 5);

			$player?->sendMessage(TF::GI . "This spawner is level " . TF::YELLOW . $level . TF::GRAY . ". To view spawner details, type " . TF::AQUA . "/spawner");
			return true;
		}

		return false;
	}
}
