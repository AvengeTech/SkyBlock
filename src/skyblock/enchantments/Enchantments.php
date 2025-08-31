<?php

namespace skyblock\enchantments;

use core\items\type\{
	TieredTool,
	Axe as AxeOverride,
	Hoe as HoeOverride,
	Pickaxe as PickaxeOverride,
	Shovel as ShovelOverride,
	Sword as SwordOverride
};
use core\utils\ItemRegistry;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\TieredTool as ItemTieredTool;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use skyblock\SkyBlock;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\enchantments\commands\{
	AddEnchantment,
	AddEssence,
	AnimatorGuide,
	GiveBook,
	GiveMaxBook,
	GiveSpecial,
	Enchanter,
	Blacksmith,
	Conjuror,
	Guide,
	Repair,
	Sign,
	Tree,
	EditItem,
	EssenceGuide,
	MyEssence,
	PouchofEssence,
	Refinery,
	SetEssence
};
use skyblock\enchantments\effects\Effects;
use skyblock\enchantments\type\ReactiveArmorEnchantment;
use skyblock\enchantments\type\ReactiveItemEnchantment;
use skyblock\fishing\event\FishingEvent;
use skyblock\SkyBlockPlayer;

class Enchantments{

	private Effects $effects;
	/** @var array(int, int) $r_cooldown */
	private array $r_cooldown = [];
	/** @var array(string, int) $pXpCache */
	private array $pXpCache = [];

	public function __construct(
		private SkyBlock $plugin
	){
		$this->effects = new Effects($plugin, $this);

		$plugin->getServer()->getCommandMap()->registerAll("enchantments", [
			new AddEnchantment($plugin, "addenchantment", "Adds enchantment to item (owner)"),
			new GiveBook($plugin, "givebook", "Give enchantment books"),
			new GiveMaxBook($plugin, "givemaxbook", "Give max enchantment books"),
			new GiveSpecial($plugin, "givespecial", "Give nametag, custom death tag or enchantment remover"),
			new Enchanter($plugin, "enchanter", "Opens the Enchanter menu"),
			new Blacksmith($plugin, "blacksmith", "Opens the Blacksmith menu"),
			new Conjuror($plugin, "conjuror", "Open the Conjuror menu"),
			new Refinery($plugin, "refinery", "Open the Refinery menu"),
			new Guide($plugin, "guide", "Opens the Enchantment Guide"),
			new AnimatorGuide($plugin, "animatorguide", "Open the Animator guide"),
			new Repair($plugin, "repair", "Repair item in your hand (GHAST+)"),
			new Sign($plugin, "sign", "Sign the item you're holding"),
			new Tree($plugin, "tree", "View skill tree progress of held item"),
			new EssenceGuide($plugin, "essenceguide", "Open the Essence Guide"),

			new SetEssence($plugin, "setessence", "Set player essence"),
			new AddEssence($plugin, "addessence", "Give player essence"),
			new MyEssence($plugin, "myessence", "Check your essence"),
			new PouchofEssence($plugin, "pouchofessence", "Create a Pouch of Essence"),

			new EditItem($plugin, "edititem", "Item editor (sn3ak only)"),
		]);

		EnchantmentRegistry::setup();
	}

	public function getEffects() : Effects { return $this->effects; }

	public function process(Event $event) : void{
		if(($event instanceof Cancellable && $event->isCancelled())) return;

		ReactiveArmorEnchantment::onReact($event);

		$player = null;
		$item = null;

		if($event instanceof EntityDamageByEntityEvent){
			$player = $event->getDamager();

			if(!($player instanceof SkyBlockPlayer && $player->isLoaded())) return;

			$player->getGameSession()?->getEnchantments()->setLastHit();

			$item = $player->getInventory()->getItemInHand();
		}elseif($event instanceof FishingEvent){
			$player = $event->getPlayer();

			if(!($player instanceof SkyBlockPlayer && $player->isLoaded())) return;

			$item = $event->getFishingRod();
		}elseif($event instanceof BlockBreakEvent){
			$player = $event->getPlayer();

			if(!($player instanceof SkyBlockPlayer && $player->isLoaded())) return;

			$item = $event->getItem();
			// $item = ($event->getItem() instanceof Pickaxe ? ItemRegistry::convertToETool($event->getItem()) : $event->getItem());
			if ($item instanceof ItemTieredTool && !($item instanceof TieredTool || $item instanceof PickaxeOverride || $item instanceof AxeOverride || $item instanceof HoeOverride || $item instanceof ShovelOverride || $item instanceof SwordOverride)) {
				$item = ItemRegistry::convertToETool($item);
			}
			ItemRegistry::fixFuckedItem($item);

			if($item instanceof Pickaxe){
			// if(TieredTool::isPickaxe($item)){
				if(!isset($this->pXpCache[$player->getName()])) $this->pXpCache[$player->getName()] = 0;

				$this->pXpCache[$player->getName()]++;

				if($this->pXpCache[$player->getName()] % 5 == 0){
					$data = new ItemData($item);
					$leveledUp = $data->addXp(mt_rand(0, 4));
					$data->send($player);

					if($leveledUp) $data->sendLevelUpTitle($player);
				}
			}
		}

		if(!is_null($player) && !is_null($item)){
			foreach($item->getEnchantments() as $ench){
				if(!$player->isTier3()){
					$name = $ench->getType()->getName();

					if($name instanceof Translatable) $name = $name->getText();
					if(strtolower($name) == 'knockback' || strtolower($name) == 'enchantment.knockback') {
						$item->removeEnchantment($ench->getType());
						$player->getInventory()->setItemInHand($item);
					}
				}

				$id = EnchantmentIdMap::getInstance()->toId($ench->getType());

				if(!isset(ED::CONVERT[$id])) continue;

				$level = $ench->getLevel();
				$item->removeEnchantment(EnchantmentIdMap::getInstance()->fromId($id), $level);
				$item->addEnchantment(($ench = EnchantmentRegistry::getEnchantment(ED::CONVERT[$id]))->getEnchantmentInstance($level));
				$player->getInventory()->setItemInHand($item);
			}
		}

		ReactiveItemEnchantment::onReact($event);
	}

	public function getItemData(Item $item) : ItemData{
		return new ItemData($item);
	}

	public function hasCooldown(Player $player) : bool{
		$cooldown = $this->r_cooldown[$player->getXuid()] ?? 0;
		return time() <= $cooldown;
	}

	public function getCooldown(Player $player) : int{
		return $this->r_cooldown[$player->getXuid()] ?? 0;
	}

	public function getCooldownFormatted(Player $player) : string{
		$seconds = $this->getCooldown($player) - time();
		$dtF = new \DateTime("@0");
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format("%a days, %h hours, %i minutes");
	}

	public function setCooldown(Player $player, int $cooldown) : void{
		$this->r_cooldown[$player->getXuid()] = time() + $cooldown;
	}
}
