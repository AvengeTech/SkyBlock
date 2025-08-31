<?php namespace skyblock\kits;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use core\utils\TextFormat as TF;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\kits\commands\KitCommand;

class Kits{

	public const KIT_STARTER = "starter";
	public const KIT_WEEKLY = "weekly";
	public const KIT_MONTHLY = "monthly";
	public const KIT_BLAZE = "blaze";
	public const KIT_GHAST = "ghast";
	public const KIT_ENDERMAN = "enderman";
	public const KIT_WITHER = "wither";
	public const KIT_ENDERDRAGON = "enderdragon";

	/** @var Kit[] $kits */
	private array $kits = [];

	public function __construct(
		private SkyBlock $plugin
	){
		$plugin->getServer()->getCommandMap()->register("kit", new KitCommand($plugin, "kit", "Equip a kit!"));

		$this->registerKits();
	}

	public function registerKits() : void{
		$this->kits = [
			self::KIT_STARTER => new Kit(
				self::KIT_STARTER,
				TF::GRAY . "Starter", [
					VanillaItems::STRING()->setCount(12),
					VanillaBlocks::SAND()->asItem()->setCount(3),
					VanillaBlocks::ICE()->asItem()->setCount(2),
					VanillaBlocks::SUGARCANE()->asItem(),
					VanillaBlocks::CACTUS()->asItem(),
					VanillaItems::LAVA_BUCKET(),
					VanillaBlocks::OAK_SAPLING()->asItem(),
				], [], 2, "default", "st"
			),
			self::KIT_WEEKLY => new Kit(
				self::KIT_WEEKLY,
				TF::GRAY . "Weekly", [
					VanillaItems::IRON_SWORD(),
					VanillaItems::IRON_PICKAXE(),
					VanillaItems::IRON_AXE(),
					VanillaItems::COOKED_CHICKEN()->setCount(16),
				], [
					0 => VanillaItems::LEATHER_CAP(),
					1 => VanillaItems::LEATHER_TUNIC(),
					2 => VanillaItems::LEATHER_PANTS(),
					3 => VanillaItems::LEATHER_BOOTS()
				], 
				24 * 7, 
				"default",
				"week"
			),
			self::KIT_MONTHLY => new Kit(
				self::KIT_MONTHLY,
				TF::GRAY . "Monthly", [
					VanillaItems::DIAMOND_SWORD(),
					VanillaItems::DIAMOND_PICKAXE(),
					VanillaItems::DIAMOND_AXE(),
					VanillaItems::STEAK()->setCount(16),
				], [
					0 => VanillaItems::IRON_HELMET(),
					1 => VanillaItems::IRON_CHESTPLATE(),
					2 => VanillaItems::IRON_LEGGINGS(),
					3 => VanillaItems::IRON_BOOTS()
				], 
				24 * 31, 
				"default",
				"mon"
			),
			self::KIT_BLAZE => new Kit(
				self::KIT_BLAZE,
				TF::GOLD . "Blaze", [
					VanillaItems::IRON_SWORD(),
					VanillaItems::IRON_PICKAXE(),
					VanillaItems::GOLDEN_PICKAXE()->setCount(2),
					VanillaItems::IRON_AXE(),
					VanillaItems::COOKED_PORKCHOP()->setCount(16),
					VanillaItems::COOKED_CHICKEN()->setCount(8),
					VanillaItems::GOLDEN_APPLE()->setCount(2),
				], [
					0 => VanillaItems::GOLDEN_HELMET(),
					1 => VanillaItems::GOLDEN_CHESTPLATE(),
					2 => VanillaItems::GOLDEN_LEGGINGS(),
					3 => VanillaItems::GOLDEN_BOOTS()
				], 
				24, 
				"blaze",
				"b"
			),
			self::KIT_GHAST => new Kit(
				self::KIT_GHAST,
				TF::WHITE . "Ghast", [
					VanillaItems::IRON_SWORD(),
					VanillaItems::IRON_PICKAXE()->setCount(2),
					VanillaItems::IRON_AXE()->setCount(2),
					VanillaItems::STEAK()->setCount(16),
					VanillaItems::COOKED_PORKCHOP()->setCount(16),
					VanillaItems::GOLDEN_APPLE()->setCount(3),
				], [
					0 => VanillaItems::CHAINMAIL_HELMET(),
					1 => VanillaItems::CHAINMAIL_CHESTPLATE(),
					2 => VanillaItems::CHAINMAIL_LEGGINGS(),
					3 => VanillaItems::CHAINMAIL_BOOTS()
				], 
				24, 
				"ghast",
				"g"
			),
			self::KIT_ENDERMAN => new Kit(
				self::KIT_ENDERMAN,
				TF::DARK_PURPLE . "Enderman", [
					VanillaItems::DIAMOND_AXE(),
					VanillaItems::IRON_PICKAXE()->setCount(3),
					VanillaItems::STEAK()->setCount(32),
					VanillaItems::GOLDEN_APPLE()->setCount(4),
				], [
					0 => VanillaItems::IRON_HELMET(),
					1 => VanillaItems::IRON_CHESTPLATE(),
					2 => VanillaItems::IRON_LEGGINGS(),
					3 => VanillaItems::IRON_BOOTS()
				], 
				24, 
				"enderman",
				"em"
			),
			self::KIT_WITHER => new Kit(
				self::KIT_WITHER,
				TF::DARK_RED . "Wither", [
					VanillaItems::DIAMOND_SWORD(),
					VanillaItems::DIAMOND_PICKAXE(),
					VanillaItems::DIAMOND_AXE(),
					VanillaItems::STEAK()->setCount(32),
					VanillaItems::COOKED_PORKCHOP()->setCount(16),
					VanillaItems::GOLDEN_APPLE()->setCount(6),
				], [
					0 => VanillaItems::DIAMOND_HELMET(),
					1 => VanillaItems::DIAMOND_CHESTPLATE(),
					2 => VanillaItems::DIAMOND_LEGGINGS(),
					3 => VanillaItems::DIAMOND_BOOTS()
				], 
				24, 
				"wither",
				"w"
			),
			self::KIT_ENDERDRAGON => new Kit(
				self::KIT_ENDERDRAGON,
				TF::BLUE . "Enderdragon", [
					VanillaItems::NETHERITE_SWORD(),
					VanillaItems::NETHERITE_SHOVEL(),
					VanillaItems::NETHERITE_PICKAXE(),
					VanillaItems::NETHERITE_AXE(),
					VanillaItems::NETHERITE_HOE(),
					VanillaItems::STEAK()->setCount(32),
					VanillaItems::COOKED_PORKCHOP()->setCount(32),
					VanillaItems::GOLDEN_APPLE()->setCount(8),
				], [
					0 => VanillaItems::NETHERITE_HELMET(),
					1 => VanillaItems::NETHERITE_CHESTPLATE(),
					2 => VanillaItems::NETHERITE_LEGGINGS(),
					3 => VanillaItems::NETHERITE_BOOTS()
				], 
				72, 
				"enderdragon", 
				"ed"
			),
		];
	}

	/** @return Kit[] */
	public function getKits() : array{
		return $this->kits;
	}

	public function getKitListString(Player $player) : string{
		/** @var SkyBlockPlayer $player */
		$string = "";
		$session = $player->getGameSession()->getKits();
		foreach($this->getKits() as $kit){
			$string .= TF::GRAY . "- ";
			if($session->hasCooldown($kit->getId()) || !$kit->hasRequiredRank($player)){
				$string .= TF::RED;
			}else{
				$string .= TF::GREEN;
			}
			$string .= $kit->getId() . "\n";
		}
		return rtrim($string);
	}

	public function getKitByName(string $name) : ?Kit{
		if(isset($this->kits[$name])){
			return clone $this->kits[$name];
		}
		return null;
	}

	public function getKitByShortName(string $name) : ?Kit{
		foreach($this->getKits() as $kit){
			if($kit->getShortName() == $name){
				return clone $kit;
			}
		}
		return null;
	}

}