<?php namespace skyblock\crates;

use pocketmine\block\{
	Block,
    BlockTypeIds,
    Concrete,
    ConcretePowder,
    Coral,
    CoralBlock,
    FloorCoralFan,
    StainedGlass,
    Wool,
};
use pocketmine\entity\{
	Location
};
use pocketmine\entity\effect\{
	EffectInstance,
	VanillaEffects
};
use pocketmine\item\{
    Dye,
    Item,
    StringToItemParser,
};
use pocketmine\player\Player;
use pocketmine\world\{
	Position,
};
use pocketmine\utils\TextFormat;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\crates\entity\{
	Crate,
	IronCrate,
	GoldCrate,
	DiamondCrate,
	EmeraldCrate,
	DivineCrate,
	VoteCrate
};
use skyblock\crates\commands\{
	AddKeys,
	KeyAll,
	ExtractKeys,
	KeyPack,
	SeeKeys,
	GiveInstantOpen
};
use skyblock\crates\prize\{
	Prize,
	PrizeVar
};
use skyblock\enchantments\item\{
	Nametag,
	CustomDeathTag,
	MaxBook,
	EnchantmentBook,
	EnchantmentRemover,
    UnboundTome
};
use skyblock\generators\item\GenBooster;
use skyblock\islands\warp\block\StonePressurePlate;

use core\Core;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use core\utils\Utils;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\network\mcpe\protocol\types\Enchant;
use skyblock\crates\event\KeyFindEvent;
use skyblock\enchantments\effects\items\EffectItem;
use skyblock\enchantments\EnchantmentData;
use skyblock\enchantments\EnchantmentRegistry;
use skyblock\item\Essence;
use skyblock\item\EssenceOfAscension;
use skyblock\item\EssenceOfKnowledge;
use skyblock\item\EssenceOfSuccess;
use skyblock\item\FireworkRocket;
use skyblock\item\Fireworks;
use skyblock\pets\item\EnergyBooster;
use skyblock\pets\item\GummyOrb;

class Crates{

	const FIND_WORDS = [
		"Wow!",
		"Boo-yah!",
		"Woah!",
		"What's this?",
		"OMG!",
		"Wowza!",
		"Sheeesh!",
	];

	const KEY_COLORS = [
		"iron" => TextFormat::WHITE,
		"gold" => TextFormat::GOLD,
		"diamond" => TextFormat::AQUA,
		"emerald" => TextFormat::GREEN,
		"divine" => TextFormat::RED,
		"vote" => TextFormat::YELLOW
	];

	public array $crates = [];
	public array $prizes = [
		CrateData::RARITY_COMMON => [],
		CrateData::RARITY_UNCOMMON => [],
		CrateData::RARITY_RARE => [],
		CrateData::RARITY_LEGENDARY => [],
		CrateData::RARITY_DIVINE => [],
	];

	public function __construct(public SkyBlock $plugin){
		/**EntityFactory::getInstance()->register(Crate::class, function(World $world, CompoundTag $nbt) : CrateEntity{
			return new CrateEntity(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ["game:crate"]);*/

		foreach (CrateData::CRATE_LOCATIONS as $id => $data) {
			$x = $data[0];
			$y = $data[1];
			$z = $data[2];
			$world = $plugin->getServer()->getWorldManager()->getWorldByName($data[3]);
			$yaw = $data[4];
			if ($world !== null) {
				$loc = new Location($x + 0.5, $y + 1, $z + 0.5, $world, $yaw, 0);
				$type = $data[5];
				$crate = null;
				if ($type === CrateData::CRATE_IRON) {
					$crate = $this->crates[$id] = new IronCrate($loc);
				} elseif ($type === CrateData::CRATE_GOLD) {
					$crate = $this->crates[$id] = new GoldCrate($loc);
				} elseif ($type === CrateData::CRATE_DIAMOND) {
					$crate = $this->crates[$id] = new DiamondCrate($loc);
				} elseif ($type === CrateData::CRATE_EMERALD) {
					$crate = $this->crates[$id] = new EmeraldCrate($loc);
				} elseif ($type === CrateData::CRATE_VOTE) {
					$crate = $this->crates[$id] = new VoteCrate($loc);
				} elseif ($type === CrateData::CRATE_DIVINE) {
					$crate = $this->crates[$id] = new DivineCrate($loc);
				}
				$crate?->spawnToAll();
			}
		}

		foreach(CratePrizes::PRIZES as $rarity => $data){
			foreach ($data as $prize => $prizeData) {
				$subRarity = $prizeData[0];
				$filterType = $prizeData[1];

				$this->prizes[$rarity][$subRarity] ??= [];

				$data = explode(":", $prize);

				$type = array_shift($data);
				if($type === "i"){
					$pre = $prize;
					$id = array_shift($data);
					$count = (count($data) == 0 ? 1 : array_shift($data));

					$prize = StringToItemParser::getInstance()->parse($id);
					$prize?->setCount($count);

					if($prize instanceof GenBooster){
						$prize->setup((int) (count($data) == 0 ? 1 : array_shift($data)));
					}elseif($prize instanceof UnboundTome){
						$prize->init((int) (count($data) == 0 ? -1 : array_shift($data)));
					}elseif($prize instanceof EnchantmentBook){
						$prize->setRarity((int) (count($data) == 0 ? 1 : array_shift($data)));
					}elseif(
						$prize instanceof EssenceOfSuccess || $prize instanceof EssenceOfKnowledge ||
						$prize instanceof EssenceOfAscension
					){
						$prize->setup((int)(count($data) == 0 ? 1 : array_shift($data)));
					}elseif($prize instanceof MaxBook){
						$prize->setup(
							(int) (count($data) == 0 ? 1 : array_shift($data)), // book type
							(int) (count($data) == 0 ? 1 : array_shift($data)), // rarity
							(int) EnchantmentData::CAT_UNIVERSAL, // placeholder category, is later set to the correct category in Prize::give()
							(int) (count($data) == 0 ? 0 : array_shift($data)), // include divine
						);
					}elseif($prize instanceof Nametag ||$prize instanceof CustomDeathTag){
						$prize->init();
					} elseif ($prize?->getTypeId() === BlockRegistry::STONE_PRESSURE_PLATE()->asItem()->getTypeId()) {
						StonePressurePlate::addData($prize);
					}elseif($prize instanceof GummyOrb){
						$prize->setup(
							(int) (count($data) == 0 ? 1 : array_shift($data)), // rarity
							(int) (count($data) == 0 ? 1 : array_shift($data)), // xp
						)->init();
					}elseif($prize instanceof GummyOrb || $prize instanceof EnergyBooster){
						$prize->setup(
							(int) (count($data) == 0 ? 1 : array_shift($data)), // rarity
							(float) (count($data) == 0 ? 1 : array_shift($data)), // energy
						)->init();
					}
					if (is_null($prize)) {
						Utils::dumpVals("Invalid prize: " . strval($pre));
						continue;
					}
				}
				if($type === "pvf"){
					$prize = new PrizeVar($data);
				}
				$this->prizes[$rarity][$subRarity][] = new Prize($prize, $rarity, $filterType);
			}
		}

		$plugin->getServer()->getCommandMap()->registerAll("crates", [
			new AddKeys($plugin, "addkeys", "Add crate keys to a player (staff)"),
			new KeyAll($plugin, "keyall", "KEYALL!!! (staff)"),
			new ExtractKeys($plugin, "extractkeys", "Turns keys into items"),
			new KeyPack($plugin, "keypack", "Key Packs (staff)"),
			new SeeKeys($plugin, "seekeys", "See how many keys you have"),
			new GiveInstantOpen($plugin, "giveinstantopen", "Give instant open (staff)"),
		]);
	}

	public function getCrates() : array{
		return $this->crates;
	}

	public function getCrateById(int $id) : ?Crate{
		return $this->crates[$id] ?? null;
	}

	public function getCrateByPosition(Position $pos) : ?Crate{
		foreach($this->crates as $crate){
			if($crate->getPosition()->equals($pos->asPosition())) return $crate;
		}
		return null;
	}

	public function getRandomPrize(int $rarity) : Prize{
		$prize = null;

		while(is_null($prize)){
			$chance = round(lcg_value() * 100, 2);
			$subRarity = match(true){
				($chance <= 35) => 0,
				($chance <= 65) => 1,
				($chance <= 85) => 2,
				($chance <= 95) => 3,
				($chance <= 99) => 4,
				($chance <= 100) => 5,
				default => 0
			};

			if(!isset($this->prizes[$rarity][$subRarity])) continue;

			$prize = $this->prizes[$rarity][$subRarity][array_rand($this->prizes[$rarity][$subRarity])];
		}

		/** @var Prize $prize */
		$prize = clone $prize;
		if (($pitem = $prize->getPrize()) instanceof Item){
			$cat = mt_rand(1, 2) === 2 ? EnchantmentData::CAT_PICKAXE : EnchantmentData::CAT_TOOL;

			if($pitem instanceof EnchantmentBook){
				$rand = mt_rand(1, 100);
				$level = match(true){
					($rand <= 40) => 1,
					($rand <= 70) => 2,
					($rand <= 85) => 3,
					($rand <= 95) => 4,
					($rand <= 100) => 5,
					default => 1
				};

				$pitem->setEnchantmentCategory($cat);
				$pitem->setup(($enchantment = EnchantmentRegistry::getRandomEnchantment(
					$pitem->getRarity(), 
					$rarity < 4 ? $pitem->getEnchantmentCategory() : mt_rand(0, 5)
				))->setStoredLevel(min($level, $enchantment->getMaxLevel())));
				$prize->prize = $pitem;
			}elseif($pitem instanceof MaxBook){
				$pitem->setEnchantmentCategory($cat);
				$pitem->init();
				$prize->prize = $pitem;
			}elseif($pitem instanceof EffectItem){
				$pitem->setup(mt_rand(1, 4));
				$prize->prize = $pitem;
			}elseif($pitem instanceof Dye){
				$pitem->setColor(DyeColor::getAll()[array_rand(DyeColor::getAll())]);
				$prize->prize = $pitem;
			}elseif($pitem instanceof Essence){
				$pitem->init();
				$prize->prize = $pitem;
			}elseif($pitem instanceof FireworkRocket){
				$pitem->setExplosion(
					Fireworks::TYPES[array_rand(Fireworks::TYPES)], 
					DyeColor::getAll()[array_rand(DyeColor::getAll())], 
					"",
					false,
					true
				)->setFlightDuration(mt_rand(1, 3));

				$prize->prize = $pitem;
			}

			if(($pBlock = $pitem->getBlock()) instanceof Block){
				if($pBlock instanceof Concrete || $pBlock instanceof ConcretePowder || $pBlock instanceof Wool || $pBlock instanceof StainedGlass){
					$pBlock->setColor(DyeColor::getAll()[array_rand(DyeColor::getAll())]);
					$prize->prize = $pBlock->asItem()->setCount($pitem->getCount());
				}
				if($pBlock instanceof CoralBlock || $pBlock instanceof FloorCoralFan || $pBlock instanceof Coral){
					$pBlock->setCoralType(CoralType::getAll()[array_rand(CoralType::getAll())]);
					$prize->prize = $pBlock->asItem()->setCount($pitem->getCount());
				}
			}
		}elseif($pitem instanceof PrizeVar && $pitem->getKey() == "tag"){
			$pitem->extra[0] = SkyBlock::getInstance()->getTags()->getRandomTag()->getName();
			$prize->prize = $pitem;
		}
		return $prize;
	}

	public function excavate(Player $player, Block $block) : bool{
		/** @var SkyBlockPlayer $player */
		$chances = [
			"iron" => 220,
			"gold" => 350,
			"diamond" => 700,
			"emerald" => 1400
		];
		if($player->getEffects()->has(VanillaEffects::HASTE())){
			$ef = $player->getEffects()->get(VanillaEffects::HASTE());
			if($ef instanceof EffectInstance){
				foreach($chances as $key => $chance){
					$chances[$key] = $chance + (($ef->getAmplifier() + 1) * 50);
				}
			}
		}
		$obtainable = [
			// Mining
			BlockTypeIds::COBBLESTONE => 0,
			BlockTypeIds::COAL_ORE => 5,
			BlockTypeIds::IRON_ORE => 5,
			BlockTypeIds::GOLD_ORE => 7,
			BlockTypeIds::LAPIS_LAZULI_ORE => 8,
			BlockTypeIds::REDSTONE_ORE => 10,
			BlockTypeIds::DIAMOND_ORE => 10,
			BlockTypeIds::NETHER_QUARTZ_ORE => 10,
			BlockTypeIds::EMERALD_ORE => 15,

			// Farming
			BlockTypeIds::WHEAT => -50,
			BlockTypeIds::CARROTS => -50,
			BlockTypeIds::POTATOES => -50,
			BlockTypeIds::SUGARCANE => -50,
			BlockTypeIds::BEETROOTS => -25,
			BlockTypeIds::BAMBOO => -15,
			BlockTypeIds::CACTUS => -10,
			BlockTypeIds::PUMPKIN => -10,
			BlockTypeIds::NETHER_WART => -5,
			BlockTypeIds::MELON => 0,
			BlockTypeIds::BROWN_MUSHROOM_BLOCK => 0,
			BlockTypeIds::RED_MUSHROOM_BLOCK => 5,
			BlockTypeIds::CHORUS_FLOWER => 10,
		];
		if(isset($obtainable[$block->getTypeId()])){
			foreach($chances as $type => $chance){
				if(mt_rand(1, $chance - $obtainable[$block->getTypeId()]) == 1){
					$event = new KeyFindEvent($player, $type, 1);
					$event->call();

					if($event->isCancelled()) return false;

					$player->getGameSession()->getCrates()->addKeys($type, $event->getAmount());
					$player->playSound("mob.chicken.hurt");
					$player->sendTitle(
						TextFormat::YELLOW . self::FIND_WORDS[array_rand(self::FIND_WORDS)], 
						TextFormat::YELLOW . "Found x" . $event->getAmount() . " " . self::KEY_COLORS[$type] . ucfirst($type) . " Key", 
						10, 
						40, 
						10
					);
					return true;
				}
			}
		}
		return false;
	}

}