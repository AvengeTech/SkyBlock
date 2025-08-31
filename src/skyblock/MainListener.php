<?php

namespace skyblock;

use core\AtPlayer;
use core\block\tile\Chest;
use pocketmine\block\{
	Bamboo,
    BambooSapling,
    BlockTypeIds,
	Cactus,
	ChorusPlant,
	Crops,
	Dirt,
	Door,
	Grass,
	Trapdoor,
	ItemFrame,
	VanillaBlocks,
	EnchantingTable,
	WallSign,
	MonsterSpawner,
	Element,
	Farmland,
	GrassPath,
	Melon,
	NetherWartPlant,
	Pumpkin,
	Sugarcane,
	tile\Container,
	utils\SaplingType,
	Wood
};
use pocketmine\block\inventory\{
	BlockInventory,
	ChestInventory,
	CraftingTableInventory,
	DoubleChestInventory,
	FurnaceInventory,
	HopperInventory,
	ShulkerBoxInventory
};
use pocketmine\block\tile\{
	Hopper as TileHopper,
	Sign,
	Tile
};
use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerCreationEvent,
	PlayerItemUseEvent,
	PlayerJoinEvent,
	PlayerQuitEvent,
	PlayerMoveEvent,
	PlayerInteractEvent,
	PlayerBucketFillEvent,
	PlayerBucketEmptyEvent,
	PlayerItemHeldEvent,
	PlayerDropItemEvent,
	PlayerItemConsumeEvent,
	PlayerChatEvent,
	PlayerToggleFlightEvent
};
use pocketmine\event\block\{
	BlockPlaceEvent,
	BlockBreakEvent,
	BrewItemEvent,
	LeavesDecayEvent,
	SignChangeEvent,
	StructureGrowEvent
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityDamageByChildEntityEvent,
	EntityDeathEvent,
	EntityTeleportEvent,
	EntityItemPickupEvent,
	EntityTrampleFarmlandEvent,
	ItemSpawnEvent
};
use pocketmine\event\inventory\{
	InventoryTransactionEvent,
	CraftItemEvent
};
use pocketmine\event\server\{
	CommandEvent,
	DataPacketReceiveEvent,
};
use pocketmine\event\world\{
	ChunkLoadEvent,
	ChunkUnloadEvent,
};
use pocketmine\inventory\{
	PlayerInventory,
	ArmorInventory,
	Inventory,
	PlayerCraftingInventory
};
use pocketmine\inventory\transaction\action\{
	SlotChangeAction
};
use pocketmine\item\{
	Food,
	Axe,
	Durable,
	Pickaxe,
	Shovel,
	Shears,
	FlintSteel,
	Fertilizer,
	PaintingItem,
	Sword,
	Armor,
	ChorusFruit,
	Hoe,
	EnderPearl,
	Snowball,
	enchantment\VanillaEnchantments,
    FireCharge,
    Item,
	ItemBlock,
	ItemTypeIds,
	StringToItemParser,
	VanillaItems
};
use pocketmine\math\{
    AxisAlignedBB,
    Facing,
	Vector3
};
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\{
	InventoryTransactionPacket,
	ServerSettingsRequestPacket,
	LevelEventPacket,
	types\LevelEvent,
	SetPlayerGameTypePacket,
};
use pocketmine\network\mcpe\protocol\{
	types\inventory\UseItemOnEntityTransactionData
};
use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\world\{
	Position,
	generator\object\TreeType,
	sound\GhastShootSound
};
use pocketmine\utils\Random;

use skyblock\block\tree\TreeFactory;
use skyblock\combat\utils\ModeManager;
use skyblock\crates\event\KeyFindEvent;
use skyblock\enchantments\{
	ItemData,
	EnchantmentData,
	EnchantmentRegistry,
    Enchantments
};
use skyblock\enchantments\item\MaxBook;
use skyblock\event\AutoInventoryCollectEvent;
use skyblock\entity\{
	ArmorStand,
	NoGravityItemEntity
};
use skyblock\fishing\item\FishingRod;
use skyblock\generators\block\{
	AutoMiner,
	OreGenerator,
	DimensionalBlock
};
use skyblock\generators\item\GenBooster;
use skyblock\generators\tile\{
	AutoMiner as TileAutoMiner,
	OreGenerator as TileOreGenerator,
	DimensionalTile
};
use skyblock\generators\ui\{
	AutoMinerUi,
	OreGeneratorUi,
	DimensionalUi
};
use skyblock\hoppers\{
	HopperBlock as Hopper,
	task\HopperStoreTask
};
use skyblock\islands\{
	IslandManager,
	warp\block\StonePressurePlate
};
use skyblock\islands\permission\Permissions;
use skyblock\islands\shop\ui\ShopCreateUi;
use skyblock\islands\shop\ui\edit\EditShopUi;
use skyblock\islands\shop\ui\view\ShopFrontUi;
use skyblock\item\{
	ExpBottle,
	FireworkRocket,
};
use skyblock\settings\SkyBlockSettings;
use skyblock\shop\item\SellWand;
use skyblock\spawners\{
	block\MobSpawner,
	entity\Mob,
	event\SpawnerKillEvent,
	tile\Spawner,
	ui\SpawnerInfoUi
};
use skyblock\shop\ui\SellChestConfirmUi;
use skyblock\trade\inventory\TradeInventory;
use skyblock\trash\tiles\TrashInventory;

use core\Core;
use core\chat\Chat;
use core\inbox\object\MessageInstance;
use core\inventory\TempInventory;
use core\staff\anticheat\session\SessionManager;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use pocketmine\entity\object\Painting;
use pocketmine\nbt\tag\CompoundTag;
use skyblock\block\RedMushroomBlock;
use skyblock\generators\event\GeneratorApplyItemEvent;
use skyblock\generators\item\Extender;
use skyblock\generators\item\Solidifier;
use skyblock\hoppers\tile\HopperTile;
use skyblock\islands\permission\IslandPermissions;
use skyblock\item\inventory\SpecialItemsInventoryHandler;
use skyblock\pets\item\PetEgg;
use skyblock\pets\Structure as PetStructure;
use skyblock\farming\Structure as FarmingStructure;
use skyblock\fishing\event\FishingEvent;

class MainListener implements Listener {

	public array $lastmsg = [];
	public array $signTapCd = [];

	private array $essenceCooldown = [];

	/** @var array<int, Vector3[]>[] */
	public static array $chestSoundCancel = [
		"open" => [],
		"close" => []
	];

	public array $dj = []; //double jump

	public function __construct(public SkyBlock $plugin) {
	}

	/**
	 * @priority HIGHEST
	 */
	public function onCreation(PlayerCreationEvent $e) {
		$e->setPlayerClass(SkyBlockPlayer::class);
	}

	public function onJoin(PlayerJoinEvent $e) {
		$this->plugin->onPreJoin($e->getPlayer());
	}

	public function onQuit(PlayerQuitEvent $e) {
		$player = $e->getPlayer();
		if ($player instanceof SkyBlockPlayer) {
			$player->getSeeInv()?->closeToAll();
			$player->getEnderInv()?->closeToAll();
		}
		//$this->plugin->onQuit($player);
	}

	public function onMove(PlayerMoveEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) return;
		$level = $player->getWorld();
		if ($player->isOnGround()) $player->toggleGlide(false);

		if ($player->atSpawn()) {
			if (isset($this->dj[$player->getName()])) {
				if ($this->dj[$player->getName()] != time()) {
					if (!($player->getWorld()->getBlockAt((int) $player->getPosition()->x, (int) ($player->getPosition()->y - 0.5), (int) $player->getPosition()->z)->asItem()->equals(VanillaItems::AIR()))) {
						unset($this->dj[$player->getName()]);
						if (!$player->getGameSession()->getCombat()->inPvPMode() && !$player->getGameSession()->getParkour()->hasCourseAttempt()) $player->setAllowFlight(true);
					}
				}
			}

			$pos = $player->getPosition();
			if ($pos->getX() > -14522 || $pos->getX() < -14801 || $pos->getZ() > 13732 || $pos->getZ() < 13404) {
				$player->teleport($this->plugin->getSpawnPosition());
				return;
			}
			if ($pos->getY() <= 109) {
				if ($pos->getX() > -14671 && $pos->getX() < -14649 && $pos->getZ() > 13572 && $pos->getZ() < 13594) {
					if ($player->isStaff() && $player->getSession()->getStaff()->inAirFromPunch()) {
						$player->teleport(new Vector3(-14638.5, 117.5, 13583.5), 90, 0);
						return;
					}
					if ($player->getArmorInventory()->getItem(1)->equals(ItemRegistry::ELYTRA(), true, false)) {
						$player->teleport(new Vector3(-14638.5, 117.5, 13583.5), 90, 0);
						$player->sendMessage(TextFormat::RI . "You cannot wear elytras in the warzone!");
						return;
					}
					$arena = $this->plugin->getCombat()->getArenas()->getArena();
					if ($arena->isLocked()) {
						$player->sendMessage(TextFormat::RI . "Arena is currently locked!");
						$player->teleport($this->plugin->getSpawnPosition(), 90, 0);
						return;
					}
					$arena->teleportTo($player);
				}
			}

			if ($player->getPosition()->getY() <= 20) {
				if ($player->getGameSession()->getParkour()->hasCourseAttempt()) {
					$player->teleport($player->getGameSession()->getParkour()->getCourseAttempt()->getLastCheckpoint());
				} else {
					$player->teleport($this->plugin->getSpawnPosition(), 90, 0);
				}
				return;
			}
		}
		foreach ($this->plugin->getLeaderboards()->getLeaderboards() as $lb) {
			$lb->doRenderCheck($player);
		}
		$session = $player->getGameSession()->getIslands();
		if ($player->getPosition()->getY() <= 0) {
			if ($session->atValidIsland()) {
				$session->getIslandAt()->teleportTo($player);
			} elseif ($this->plugin->getKoth()->inGame($player)) {
				$this->plugin->getKoth()->getGameByPlayer($player)->teleportTo($player);
			} elseif (($lms = $this->plugin->getLms())->inGame($player)) {
				$lms->getGameByPlayer($player)->teleportTo($player, false);
				$lms->getGameByPlayer($player)->removePlayer($player);
				$lms->getGameByPlayer($player)->addSpectator($player);
				$player->sendMessage(TextFormat::RI . "You died lol");
			} elseif ($this->plugin->getCombat()->getArenas()->inArena($player)) {
				$this->plugin->getCombat()->getArenas()->getArena()->teleportTo($player);
			} else {
				$player->teleport($this->plugin->getSpawnPosition());
			}
			return;
		}
		if ($session->atValidIsland()) {
			$island = $session->getIslandAt();
			if (!$island->inZone($player)) {
				$e->cancel();
				if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
					$player->sendMessage(TextFormat::RI . "Move limit reached. " . ($island->getSizeLevel() < 15 ? "This island must be at least level " . TextFormat::YELLOW . $island->getNextSizeUpgrade() . TextFormat::GRAY . " to move here." : ""));
					$this->lastmsg[$player->getXuid()] = time();
				}
				return;
			}
		}
		$ksession = $player->getGameSession()->getKoth();
		if ($ksession->inGame()) {
			$game = $ksession->getGame();
			if (!$game->isInBorder($player)) {
				$game->nudge($player);
				$player->sendTip(TextFormat::RED . "Please stay in the KOTH arena!");
			}
		}
		if (($a = $this->plugin->getCombat()->getArenas())->inArena($player)) {
			$arena = $a->getArena();
			if (!$arena->isInBorder($player)) {
				$arena->goBack($player);
			}
		}
	}


	public function onItemUse(PlayerItemUseEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) return;
		$item = $e->getItem();

		if ($this->plugin->getCombat()->getArenas()->inArena($player)) {
			if ($item instanceof Armor) {
				if ($item->equals(ItemRegistry::ELYTRA(), true, false)) {
					$e->cancel();
					return;
				}
				return;
			}
			if ($item instanceof FishingRod) {
				$e->cancel();
				// using $item->getVanillaName() for forwards compatibility with multiple item checks.
				$player->sendMessage(TextFormat::RN . "You can't use " . $item->getVanillaName() . "s in warzone!");
				return;
			}
		}

		if ($item instanceof FishingRod && $player->getGameSession()?->getCombat()->inPvPMode()) {
			$e->cancel();
			// using $item->getVanillaName() for forwards compatibility with multiple item checks.
			$player->sendMessage(TextFormat::RN . "You can't use " . $item->getVanillaName() . "s in PvP!");
		}

		if (!$item instanceof Food) {
			if ($item instanceof MaxBook) {
				$e->cancel();
				if ($item->isInitiated()) {
					$needed = $item->getCost();
					if ($player->getXpManager()->getXpLevel() < $needed) {
						$player->sendMessage(TextFormat::YN . "You don't have enough XP Levels to redeem this book! Required: " . TextFormat::YELLOW . $needed . " XP Levels");
						return;
					}
					if ($item->redeem($player)) {
						$player->getXpManager()->subtractXpLevels($needed);
						$e->cancel();
					} else {
						$player->sendMessage(TextFormat::YN . "You must free up space in your inventory before redeeming this!");
					}
				}
			}

			if ($this->plugin->getCombat()->getArenas()->inArena($player) || $player->getGameSession()->getLms()->inGame()) {
				if ($item instanceof EnderPearl) {
					$player->sendMessage(TextFormat::RI . "Ender pearls cannot be thrown in the warzone or events!");
					$e->cancel();
					return;
				}
			}

			$session = $player->getGameSession()->getIslands();
			if (!$session->atValidIsland()) {
				if ($item instanceof FishingRod) {
					if ($player->getGameSession()->getKoth()->inGame()) {
						$e->cancel();
						return;
					}
				}
				return;
			}
			$island = $session->getIslandAt();
			/** @var IslandPermissions $ip */
			$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

			if ($item instanceof ExpBottle && !$player->isSneaking() && !$perm->getPermission(Permissions::THROW_XP_BOTTLES)) {
				$e->cancel();
				return;
			}

			if ($item instanceof Snowball && !$perm->getPermission(Permissions::THROW_SNOWBALLS)) {
				$e->cancel();
				return;
			}

			if ($item instanceof EnderPearl && !$perm->getPermission(Permissions::THROW_ENDER_PEARLS)) {
				$e->cancel();
				return;
			}

			if ($item instanceof FishingRod && !$perm->getPermission(Permissions::CAST_FISHING_ROD)) {
				$e->cancel();
				return;
			}

			if (($item instanceof Shovel || $item instanceof Hoe) && !$perm->getPermission(Permissions::EDIT_BLOCKS)) {
				$e->cancel();
				return;
			}
			/**if(!$perm->getPermission(Permissions::EDIT_BLOCKS)){
			$e->cancel();
			return;
			}*/
		}
	}

	public function onBrewPotion(BrewItemEvent $e) {
		$e->cancel();
	}

	public function onInteract(PlayerInteractEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) return;
		$block = $e->getBlock();
		$item = $e->getItem();

		if ($player->atSpawn()) {
			if ($player->isSn3ak() || $item instanceof FireworkRocket || $item instanceof PetEgg) return;
			$e->cancel();
			return;
		}

		$ssession = $player->getGameSession()->getSpawners();
		$session = $player->getGameSession()->getIslands();
		if (!$session->atValidIsland()) {
			if ($ssession->isToggled()) $ssession->toggle();
			if (Core::thisServer()->isTestServer()) {
				return;
			}
			$e->cancel();
			return;
		}
		$island = $session->getIslandAt();
		/** @var IslandPermissions $ip */
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if (!$island->inZone($block)) {
			$e->cancel();
			return;
		}

		if ($block instanceof EnchantingTable && $e->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
			$e->cancel();
			return;
		}

		if (($item instanceof FlintSteel || 
				(($block instanceof Grass || $block instanceof Dirt || $block instanceof Farmland || $block instanceof GrassPath) && ($item instanceof Hoe || $item instanceof Shovel)) ||
				$item instanceof PaintingItem ||
				$item instanceof FireCharge ||
				(($block instanceof Wood) && ($item instanceof Axe))) &&
			!$perm->getPermission(Permissions::EDIT_BLOCKS)
		) {
			$e->cancel();
			$player->sendMessage(TextFormat::RI . "You don't have permission to edit blocks on this island!");
			return;
		}

		//HAXXXXX
		if ($item instanceof Fertilizer && !$block instanceof BambooSapling && str_contains(strtolower($block->getName()), "sapling")) {
			if (!$perm->getPermission(Permissions::EDIT_BLOCKS)) {
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You don't have permission to edit blocks on this island!");
				return;
			}
			$reflect = new \ReflectionClass($block);
			$property = $reflect->getProperty('saplingType');
			$property->setAccessible(true);
			$saplingType = $property->getValue($block);

			switch ($saplingType) {
				default:

					break;
				case SaplingType::DARK_OAK:
					$random = new Random(mt_rand());
					$tree = TreeFactory::get($random, TreeType::DARK_OAK);
					$transaction = $tree?->getBlockTransaction($block->getPosition()->getWorld(), $block->getPosition()->getFloorX(), $block->getPosition()->getFloorY(), $block->getPosition()->getFloorZ(), $random);
					if ($transaction === null) {
						return false;
					}

					$ev = new StructureGrowEvent($block, $transaction, $player);
					$ev->call();
					if (!$ev->isCancelled()) {
						$transaction->apply();
						$item->pop();
						$player->getInventory()->setItemInHand($item);
						break;
					}
					$e->cancel();
					break;
			}
		}

		if ($block instanceof Door || $block instanceof Trapdoor) {
			if (!$perm->getPermission(Permissions::OPEN_DOORS)) {
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You don't have permission to open doors on this island!");
				return;
			}
		}

		if ($block instanceof ItemFrame) {
			if (!$perm->getPermission(Permissions::EDIT_ITEM_FRAMES)) {
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You don't have permission to edit item frames on this island!");
				return;
			}
		}

		if ($session->inWarpMode()) {
			$e->cancel();
			if (!$perm->isOwner() && !$perm->getPermission(Permissions::EDIT_WARPS)) {
				$session->setWarpMode();
				$player->sendMessage(TextFormat::RI . "You don't have permission to edit warps on this island!");
				return;
			}
			$warp = $session->getWarpMode();
			if (($wm = $warp->getWarpManager())->getIsland()->getWorldName() !== $island->getWorldName()) {
				$session->setWarpMode();
				return;
			}
			if (count($wm->getWarps()) >= $wm->getWarpLimit()) {
				$session->setWarpMode();
				$player->sendMessage(TextFormat::RI . "This island has reached the max warp limit!");
			}
			if ($wm->getWarp($warp->getName()) !== null) {
				$session->setWarpMode();
				$player->sendMessage(TextFormat::RI . "Warp with this name already exists, please choose another name!");
				return;
			}
			$warp->updateLocation($block->getPosition()->getX(), $block->getPosition()->getY() + 1, $block->getPosition()->getZ(), -1);
			$island->getWarpManager()->addWarp($warp);
			$session->setWarpMode();
			$player->sendMessage(TextFormat::GI . "Successfully added island warp '" . $warp->getName() . TextFormat::RESET . TextFormat::GRAY . "'");
			return;
		}

		$tile = $block->getPosition()->getWorld()->getTile($block->getPosition());

		if ($session->inShopMode()) {
			if ($tile instanceof Sign && $block instanceof WallSign) {
				$e->cancel();
				$sm = $island->getShopManager();
				foreach ($sm->getShops() as $shop) {
					if ($shop->getPosition()->equals($tile->getPosition())) {
						$player->showModal(new EditShopUi($player, $shop));
						return;
					}
				}
				if (count($sm->getShops()) >= $sm->getShopLimit()) {
					$player->sendMessage(TextFormat::RI . "This island has reached the max amount of sign shops for it's level!");
					return;
				}

				$player->showModal(new ShopCreateUi($tile));
				return;
			}
		}

		if ($tile instanceof Sign) {
			if (isset($this->signTapCd[$player->getName()])) {
				if (microtime(true) - $this->signTapCd[$player->getName()] < 0.25) return;
			}
			$this->signTapCd[$player->getName()] = microtime(true);

			$text = $tile->getText();
			if (strtolower($text->getLine(0)) === "[warp]") {
				$e->cancel();
				$warp = $island->getWarpManager()->getWarp($text->getLine(1));
				if ($warp !== null) {
					if (!$perm->getPermission(Permissions::USE_WARPS)) {
						$player->sendMessage(TextFormat::RI . "You do not have permission use warps on this island!");
						return;
					}
					if ($warp->getHierarchy() > $perm->getHierarchy()) {
						$player->sendMessage(TextFormat::RI . "You do not have permission to visit this warp!");
						return;
					}
					$warp->teleportTo($player);
				}
				return;
			}
			if (strtolower($text->getLine(0)) === "[spawn]") {
				$e->cancel();
				$island->teleportTo($player);
				return;
			}
			if (strtolower($text->getLine(0)) === "[sellhand]") {
				$e->cancel();
				$isession = $player->getGameSession()->getIslands();
				$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
				if ($island === null) {
					$player->sendMessage(TextFormat::RI . "You can only use the island shop for the last island you visited!");
					return;
				}
				$perm = $island->getPermissions()->getPermissionsBy($player);
				if ($perm === null || !$perm->getPermission(Permissions::USE_SHOP)) {
					$player->sendMessage(TextFormat::RI . "You do not have permission to use this island's shop!");
					return;
				}

				$shop = $this->plugin->getShops();
				if (($price = $shop->sellHand($player)) == -1) {
					$player->sendMessage(TextFormat::RI . "Item in hand isn't available for sale at your island level.");
					return;
				}
				return;
			}
			if (strtolower($text->getLine(0)) === "[sellall]") {
				$e->cancel();
				if ($player->getRank() == "default") {
					$player->sendMessage(TextFormat::RI . "You must have a premium rank to sell all items in your inventory! Purchase one at " . TextFormat::YELLOW . "store.avengetech.net");
					return;
				}

				$isession = $player->getGameSession()->getIslands();
				$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();
				if ($island === null) {
					$player->sendMessage(TextFormat::RI . "You can only use the island shop for the last island you visited!");
					return;
				}
				$perm = $island->getPermissions()->getPermissionsBy($player);
				if ($perm === null || !$perm->getPermission(Permissions::USE_SHOP)) {
					$player->sendMessage(TextFormat::RI . "You do not have permission to use this island's shop!");
					return;
				}

				$shop = $this->plugin->getShops();
				$array = $shop->sellInventory($player);
				if ($array["count"] == 0) {
					$player->sendMessage(TextFormat::RI . "No items in your inventory were able to be sold.");
					return;
				}

				$player->sendMessage(TextFormat::GI . "Sold " . $array["count"] . " items in inventory for " . TextFormat::AQUA . $array["price"] . " Techits");

				return;
			}
			if ($block instanceof WallSign && ($shop = $island->getShopManager()->getShop($tile)) !== null) {
				$e->cancel();
				if (!$perm->getPermission(Permissions::USE_SIGN_SHOPS)) {
					$player->sendMessage(TextFormat::RI . "You do not have permission to use sign shops on this island!");
					return;
				}
				if ($shop->getHierarchy() > $perm->getHierarchy()) {
					$player->sendMessage(TextFormat::RI . "You do not have permission to use this sign shop!");
					return;
				}

				$player->showModal(new ShopFrontUi($player, $shop));
				return;
			}
			return;
		}

		if ($player->inGenMode()) {
			if ($e->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
				if ($tile instanceof TileOreGenerator) {
					if($item instanceof GenBooster){
						$ev = new GeneratorApplyItemEvent($tile, $player, $item);
						$ev->call();

						if (!$player->isSneaking()) {
							$item->pop();
							$tile->addBoost($value = $item->getValue());
							$player->getInventory()->setItemInHand($item);
						} else {
							$tile->addBoost($value = ($item->getValue() * $item->getCount()));
							$player->getInventory()->setItemInHand(VanillaBlocks::AIR()->asItem());
						}

						$player->sendMessage(TextFormat::GI . "Applied " . TextFormat::YELLOW . $value . " boosts " . TextFormat::GRAY . "to this ore generator!");
					}elseif($item instanceof Extender){
						if(
							$item->getType() === Extender::TYPE_HORIZONTAL && $item->getLevel() - 1 !== $tile->getHorizontalExtender() || 
							$item->getType() === Extender::TYPE_VERTICAL && $item->getLevel() - 1 !== $tile->getVerticalExtender()
						){
							$player->sendMessage(TextFormat::RI . "You must have apply the previous extender before applying this one.");
							$e->cancel();
							return;
						}
						
						if(
							$item->getType() === Extender::TYPE_HORIZONTAL && $tile->getHorizontalExtender() >= 2 || 
							$item->getType() === Extender::TYPE_VERTICAL && $tile->getVerticalExtender() >= 2
						){
							$player->sendMessage(TextFormat::RI . "You can not apply this extender, ore generator already has the max extender.");
							$e->cancel();
							return;
						}

						$ev = new GeneratorApplyItemEvent($tile, $player, $item);
						$ev->call();

						$item->pop();
						$player->getInventory()->setItemInHand($item);
						
						($item->getType() === Extender::TYPE_HORIZONTAL ? $tile->setHorizontalExtender($item->getLevel()) : $tile->setVerticalExtender($item->getLevel()));
					}elseif($item instanceof Solidifier){
						if($item->getLevel() < $tile->getSolidifierLevel()){
							$player->sendMessage(TextFormat::RI . "You already have a higher level solidifier applied on this generator.");
							$e->cancel();
							return;
						}

						$ev = new GeneratorApplyItemEvent($tile, $player, $item);
						$ev->call();

						$item->pop();
						$player->getInventory()->setItemInHand($item);

						if($item->getLevel() === $tile->getSolidifierLevel()){
							$tile->addSolidifierRuns($item->getRuns());
						}else{
							$tile->setSolidifierLevel($item->getLevel());
							$tile->setSolidifierRuns($item->getRuns());
						}
					}else{
						if ($tile->getLevel() === 0) {
							$e->cancel();
							return;
						}
						$player->showModal(new OreGeneratorUi($player, $tile));
					}
					$e->cancel();
					return;
				}elseif($tile instanceof DimensionalTile) {
					if ($item instanceof GenBooster){
						$ev = new GeneratorApplyItemEvent($tile, $player, $item);
						$ev->call();

						$item->pop();
						$tile->addBoost($value = $item->getValue());
						$player->getInventory()->setItemInHand($item);
						$player->sendMessage(TextFormat::GI . "Applied " . TextFormat::YELLOW . $value . " boosts " . TextFormat::GRAY . "to this dimensional block!");
					} else {
						$player->showModal(new DimensionalUi($player, $tile));
					}
					$e->cancel();
					return;
				}elseif($tile instanceof TileAutoMiner){
					if($item instanceof Extender){
						if(
							$item->getType() === Extender::TYPE_HORIZONTAL && $item->getLevel() - 1 !== $tile->getHorizontalExtender() || 
							$item->getType() === Extender::TYPE_VERTICAL && $item->getLevel() - 1 !== $tile->getVerticalExtender()
						){
							$player->sendMessage(TextFormat::RI . "You must have apply the previous extender before applying this one.");
							$e->cancel();
							return;
						}
						
						if(
							$item->getType() === Extender::TYPE_HORIZONTAL && $tile->getHorizontalExtender() >= 2 || 
							$item->getType() === Extender::TYPE_VERTICAL && $tile->getVerticalExtender() >= 2
						){
							$player->sendMessage(TextFormat::RI . "You can not apply this extender, autominer already has the max extender.");
							$e->cancel();
							return;
						}

						$ev = new GeneratorApplyItemEvent($tile, $player, $item);
						$ev->call();

						$item->pop();
						$player->getInventory()->setItemInHand($item);
						
						($item->getType() === Extender::TYPE_HORIZONTAL ? $tile->setHorizontalExtender($item->getLevel()) : $tile->setVerticalExtender($item->getLevel()));
					}else{
						$player->showModal(new AutoMinerUi($tile));
					}
					$e->cancel();
				}
			}
		}elseif(!$player->inGenMode() && ($tile instanceof TileOreGenerator || $tile instanceof DimensionalTile)) {
			$e->cancel();
			if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
				$player->sendMessage(TextFormat::RI . "You must be in gen mode to edit generator blocks! Use " . TextFormat::YELLOW . "/gen " . TextFormat::GRAY . "to activate!");
				$this->lastmsg[$player->getXuid()] = time();
			}
			return;
		}
		/**if($e->getAction() == PlayerInteractEvent::LEFT_CLICK_BLOCK && $block instanceof HopperBlock){
		$player->isSneaking() ? $block->onScheduledUpdateNew() : $block->onScheduledUpdate();
		$below = $block->getLowestFreeContainer($e->getItem());
		if($below instanceof \pocketmine\block\tile\Container){
		$e->cancel();
		$below->getPosition()->getWorld()->addParticle($below->getPosition(), new \pocketmine\world\particle\BlockBreakParticle($below->getBlock()));
		}
		}*/
		if ($ssession->isToggled()) {
			if ($tile instanceof Spawner) {
				if (!$perm->getPermission(Permissions::EDIT_SPAWNERS)) {
					$player->sendMessage(TextFormat::RI . "You cannot edit this island's spawners!");
				} else {
					$player->showModal(new SpawnerInfoUi($tile));
				}
				//$ssession->toggle();
				$e->cancel();
			}
		}
		if (($tile instanceof Container) && !($perm->isOwner() || $perm->getPermission(Permissions::OPEN_CONTAINERS) || $player->isStaff())) {
			$player->sendMessage(TextFormat::RI . "You do not have permission to open containers at this island!");
			$e->cancel();
			return;
		}
		if (
			$tile instanceof Chest && (
				($sw = ($item instanceof SellWand && $item->isInitiated())) ||
				$this->plugin->getShops()->inChestMode($player)
			) && (
				$perm->isOwner() ||
			$perm->getPermission(Permissions::USE_SELL_CHEST) ||
				$player->isTier3()
			)
		) {
			$player->showModal(new SellChestConfirmUi($player, $tile, $sw));
			$e->cancel();
		}

		$this->plugin->getEnchantments()->process($e);
	}

	public function onConsume(PlayerItemConsumeEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if (!$player->isLoaded()) {
			$e->cancel();
			return;
		}

		$item = $e->getItem();
		$isession = $player->getGameSession()?->getIslands();

		if (is_null($isession)) {
			$e->cancel();
			return;
		}

		if (!$isession->atValidIsland()) {
			if ($item instanceof ChorusFruit && !$player->isTier3()) {
				$player->sendMessage(TextFormat::RI . "You can only eat chorus fruit at an island!");
				$e->cancel();
				return;
			}
		}




		/**if ($item instanceof GoldenAppleEnchanted) {
			if ($player->hasGappleCooldown()) {
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You have an enchanted golden apple cooldown! You may eat another one in " . TextFormat::AQUA . $player->getGappleCooldown() . " seconds");
			} else {
				$player->setGappleCooldown();
			}
		}*/
	}

	/**
	 * @priority HIGH
	 */
	public function onPlace(BlockPlaceEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) return;
		/** @var ItemBlock $item */
		$item = $e->getItem();

		if ($player->atSpawn()) {
			if(($player->isSn3ak() || $player->isTier3()) && $player->getGamemode()->equals(GameMode::CREATIVE())) return;
			
			$e->cancel();
			return;
		}

		$session = $player->getGameSession()->getIslands();
		if (!$session->atValidIsland()) {
			if (Core::thisServer()->isTestServer()) {
				return;
			}
			$e->cancel();
			return;
		}
		$island = $session->getIslandAt();
		/** @var IslandPermissions $ip */
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if (!$perm->getPermission(Permissions::EDIT_BLOCKS)) {
			$e->cancel();
			return;
		}
		foreach ($e->getTransaction()->getBlocks() as [$x, $y, $z, $block]) {
			if ($block instanceof ItemFrame) {
				if (!$perm->getPermission(Permissions::EDIT_ITEM_FRAMES)) {
					$e->cancel();
					$player->sendMessage(TextFormat::RI . "You don't have permission to edit item frames on this island!");
					return;
				}
			}

			if (!$island->inZone($block)) {
				$e->cancel();
				if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
					$player->sendMessage(TextFormat::RI . "Build limit reached. " . ($island->getSizeLevel() < 15 ? "This island must be at least level " . TextFormat::YELLOW . $island->getNextSizeUpgrade() . TextFormat::GRAY . " for you to build here!" : ""));
					$this->lastmsg[$player->getXuid()] = time();
				}
				return;
			}
			if (
				$block instanceof Element ||
				$block instanceof AutoMiner ||
				$block instanceof OreGenerator ||
				$block instanceof DimensionalBlock
			) {
				if (!$perm->getPermission(Permissions::EDIT_GEN_BLOCKS)) {
					$e->cancel();
					if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
						$player->sendMessage(TextFormat::RI . "You are not allowed to modify this island's generator blocks!");
						$this->lastmsg[$player->getXuid()] = time();
					}
					return;
				}
				if (!$player->inGenMode()) {
					$e->cancel();
					if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
						$player->sendMessage(TextFormat::RI . "You must be in gen mode to edit generator blocks! Use " . TextFormat::YELLOW . "/gen " . TextFormat::GRAY . "to activate!");
						$this->lastmsg[$player->getXuid()] = time();
					}
					return;
				}
				if (!$island->addGen()) {
					$e->cancel();
					$player->sendMessage(TextFormat::RI . "Generator block cap reached. " . ($island->getSizeLevel() < 15 ? "Upgrade your island to place more." : ""));
					$this->lastmsg[$player->getXuid()] = time();
					return;
				}
			}
			if($block instanceof MobSpawner){
				if(!$perm->getPermission(Permissions::EDIT_SPAWNERS)) {
					$e->cancel();
					$player->sendMessage(TextFormat::RI . "You cannot place spawners on this island!");
					return;
				}
				if(!$island->addSpawner()) {
					$e->cancel();
					$player->sendMessage(TextFormat::RI . "Spawner cap reached. " . ($island->getSizeLevel() < 15 ? "Upgrade your island to place more." : ""));
					$this->lastmsg[$player->getXuid()] = time();
					return;
				}
			}
			if ($block instanceof Hopper) {
				if (!$island->addHopper()) {
					$e->cancel();
					$player->sendMessage(TextFormat::RI . "Hopper cap reached. " . ($island->getSizeLevel() < 10 ? "Upgrade your island to place more." : ""));
					$this->lastmsg[$player->getXuid()] = time();
					return;
				}
				$task = new HopperStoreTask($block);
				$this->plugin->getScheduler()->scheduleDelayedTask($task, 5);
			}
		}
	}

	public function onLeaveDecay(LeavesDecayEvent $e) {
		$name = $e->getBlock()->getPosition()->getWorld()->getDisplayName();
		if (!IslandManager::isIslandWorld($name)) {
			$e->cancel();
		}
	}

	public function onSign(SignChangeEvent $e) {
		$sign = $e->getSign();
		$sign->setWaxed(true);
	}

	public function onHeldItem(PlayerItemHeldEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) return;
		$session = $player->getGameSession()->getFishing();
		if ($session->isFishing()) {
			$session->getHook()->flagForDespawn();
			$session->setFishing();
			$session->setHooked();
		}
	}

	public function onCmd(CommandEvent $e) {
		$player = $e->getSender();
		if (!($player instanceof SkyBlockPlayer)) return;
		if (!$player->isLoaded()) return;

		$m = $e->getCommand();
		$m1 = str_replace("/", "", explode(" ", $m)[0]);

		$csession = $player->getGameSession()->getCombat();
		if ($csession->getCombatMode()->inCombat()) {
			foreach (ModeManager::DISABLED_COMMANDS as $command) {
				if ($m1 === $command) {
					$e->cancel();
					$player->sendMessage(TextFormat::RI . "You cannot use this command in combat!");
					return;
				}
			}
		}
	}

	/**
	 * @priority HIGH
	 * 
	 * @handleCancelled
	 */
	public function onChat(PlayerChatEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) return;

		$message = $e->getMessage();

		if (($game = $this->plugin->getGames()->getCurrentChatGame()) !== null) {
			if (str_contains($e->getMessage(), $game->getAnswer())) {
				$game->winner($player);
			}
		}

		if ($player->isMuted()) return;

		if ($player->getSession()->getStaff()->inStaffChat()) {
			return;
		}

		if (($gs = $player->getGameSession())->getSettings()->getSetting(SkyBlockSettings::ISLAND_CHAT)) {
			if ($gs->getIslands()->atValidIsland()) {
				$gs->getIslands()->getIslandAt()->islandChat($player, ($player->hasRank() ? Chat::convertWithEmojis($player->getLastMessage()) : $player->getLastMessage()));
				$e->cancel();
				return;
			}
		}
	}

	public function onToggleFlight(PlayerToggleFlightEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		$flying = $e->isFlying();
		if ($flying && !$player->inFlightMode() && !$player->isCreative()) {
			$e->cancel();
			$player->setFlying(false);
			$player->setAllowFlight(false);
			$gm = $player->getGamemode();
			$player->getNetworkSession()->sendDataPacket(SetPlayerGameTypePacket::create(TypeConverter::getInstance()->coreGameModeToProtocol(GameMode::CREATIVE())));
			$player->getNetworkSession()->sendDataPacket(SetPlayerGameTypePacket::create(TypeConverter::getInstance()->coreGameModeToProtocol($gm)));

			$dv = $player->getDirectionVector();
			$player->getSession()->getStaff()->inAirFromPunch = true;
			$player->knockback($dv->x, $dv->z, 1.3);
			if (!$player->isVanished() && $player->isLoaded()) {
				if (($cs = $player->getSession()->getCosmetics())->hasEquippedDoubleJump()) {
					$cs->getEquippedDoubleJump()->activate($player);
				} else {
					$player->getWorld()->addSound($player->getPosition(), new GhastShootSound());
				}
			}
			$this->dj[$player->getName()] = time();
		}
	}

	public function onBreak(BlockBreakEvent $e) : void{
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(!$player->isLoaded()){
			$e->cancel(); 
			return;
		}

		$block = $e->getBlock();

		if($player->atSpawn()){
			if(($player->isSn3ak() || $player->isTier3()) && $player->getGamemode()->equals(GameMode::CREATIVE())) return;
			
			$e->cancel();
			return;
		}

		$session = $player->getGameSession()->getIslands();

		if(!$session->atValidIsland()){
			if(Core::thisServer()->isTestServer()) return;

			$e->cancel();
			return;
		}

		$island = $session->getIslandAt();
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if(!$perm->getPermission(Permissions::EDIT_BLOCKS)){
			if(
				!$perm->getPermission(Permissions::EDIT_ORE_FROM_ORE_GENS) ||
				!$block->getSide(Facing::DOWN) instanceof OreGenerator
			){
				$e->cancel();
				return;
			}
		}

		if($block instanceof StonePressurePlate){
			if(!$perm->getPermission(Permissions::EDIT_WARP_PADS)){
				$e->cancel();
				return;
			}
		}

		$tile = $block->getPosition()->getWorld()->getTile($block->getPosition());

		if($tile instanceof Container){
			if(!$perm->getPermission(Permissions::OPEN_CONTAINERS)){
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You don't have permission to open containers on this island!");
				return;
			}

			if($tile instanceof Chest || $tile instanceof Sign){
				$shop = ($sm = $island->getShopManager())->getShopByChest($tile);

				if(!is_null($shop)){
					if(!$perm->getPermission(Permissions::EDIT_SIGN_SHOPS)){
						$e->cancel();
						$player->sendMessage(TextFormat::RI . "You don't have permission to edit sign shops on this island!");
						return;
					}

					if(!$session->inShopMode()){
						$e->cancel();
						$player->sendMessage(TextFormat::RI . "You can only edit sign shops in shop mode!");
						return;
					}

					if($shop->getBank() > 0){
						$e->cancel();
						$player->sendMessage(TextFormat::RI . "Shop's bank must be fully cleared before removal");
						return;
					}

					$sm->removeShop($shop);
					$player->sendMessage(TextFormat::GI . "Shop has been deleted.");
				}
			}
		}

		if($block instanceof ItemFrame){
			if(!$perm->getPermission(Permissions::EDIT_ITEM_FRAMES)){
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You don't have permission to edit item frames on this island!");
				return;
			}
		}

		if(
			$block instanceof Element ||
			$block instanceof AutoMiner ||
			$block instanceof OreGenerator ||
			$block instanceof DimensionalBlock
		){
			$e->setInstaBreak(true);

			if(!$perm->getPermission(Permissions::EDIT_GEN_BLOCKS)){
				$e->cancel();

				if(!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
					$player->sendMessage(TextFormat::RI . "You are not allowed to modify this island's generator blocks!");
					$this->lastmsg[$player->getXuid()] = time();
				}
				return;
			}
			if(!$player->inGenMode()){
				$e->cancel();

				if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
					$player->sendMessage(TextFormat::RI . "You must be in gen mode to edit generator blocks! Use " . TextFormat::YELLOW . "/gen " . TextFormat::GRAY . "to activate!");
					$this->lastmsg[$player->getXuid()] = time();
				}
				return;
			}
			if(
				!is_null($tile) &&
				($tile instanceof TileOreGenerator || $tile instanceof DimensionalTile) &&
				isset($tile->created) &&
				$tile->created + 1 > microtime(true)
			){
				$e->cancel();
				return;
			}
			$island->takeGen();
		}
		if(
			$block instanceof MobSpawner ||
			$block->getTypeId() == BlockTypeIds::MONSTER_SPAWNER
		){
			if(!$perm->getPermission(Permissions::EDIT_SPAWNERS)){
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You cannot break spawners on this island!");
				return;
			}
			$island->takeSpawner();
		}

		$this->plugin->getEnchantments()->process($e);
		$stsession = $player->getGameSession()->getSettings();

		$item = $player->getInventory()->getItemInHand();
		$data = new ItemData($item);
		$xp = $e->getXpDropAmount();
		if(($xpLvl = $data->getTreeLevel(ItemData::SKILL_EXP)) > 0){
			$xp *= ItemData::SKILL_TREES[ItemData::SKILL_EXP][$xpLvl];
		}
		if($stsession->getSetting(SkyBlockSettings::AUTO_XP)){
			$e->setXpDropAmount(0);

			if($xp !== 0) {
				if($item instanceof Durable && $item->hasEnchantment(VanillaEnchantments::MENDING())){
					$repairAmount = min($item->getDamage(), $xp * 2);
					$item->setDamage($item->getDamage() - $repairAmount);
					$xp -= (int) ceil($repairAmount / 2);

					$player->getInventory()->setItemInHand($item);
				}
				$player->getXpManager()->addXp((int) $xp);
				$player->getWorld()->broadcastPacketToViewers($player->getPosition(), LevelEventPacket::create(LevelEvent::SOUND_ORB, 0, $player->getPosition()));
			}
		}

		$drops = $e->getDrops();
		$pSession = $player->getGameSession()->getPets();
		$pet = $pSession->getActivePet();

		// PET BUFF
		if(
			$block instanceof Crops || $block instanceof Bamboo ||
			$block instanceof Melon || $block instanceof Pumpkin || 
			$block instanceof Sugarcane || $block instanceof Cactus || 
			$block instanceof RedMushroomBlock || $block instanceof NetherWartPlant || 
			$block instanceof ChorusPlant
		){
			$cooldown = false;

			if($block instanceof Sugarcane){
				$cooldown = true;
			}elseif(
				$block instanceof NetherWartPlant && $block->getAge() < NetherWartPlant::MAX_AGE || 
				$block instanceof Crops && $block->getAge() < Crops::MAX_AGE
			){
				$cooldown = true;
			}

			if(!$cooldown || !isset($this->essenceCooldown[$player->getXuid()]) || time() - $this->essenceCooldown[$player->getXuid()] >= 5){
				if(round(lcg_value() * 100, 2) <= ($cooldown ? 3.5 : 9.5)){
					if($cooldown) $this->essenceCooldown[$player->getXuid()] = time();

					$essence = (round(lcg_value() * 100, 2) <= 85 ? mt_rand(1, 5) : mt_rand(5, 10));

					if ($item->hasEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())) {
						$essence += $item->getEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())->getLevel() + 1;
					}

					if($item instanceof Hoe && ($essenceLvl = $data->getTreeLevel(ItemData::SKILL_ESSENCE)) > 0){
						$essence *= ItemData::SKILL_TREES[ItemData::SKILL_ESSENCE][$essenceLvl];
					}
					
					$essence = (int) $essence;
					$session = $player->getGameSession()->getEssence();
					$session->addEssence($essence);
					$player->sendTip(TextFormat::AQUA . "You found {$essence} Essence");
					$player->getWorld()->broadcastPacketToViewers($player->getPosition(), LevelEventPacket::create(LevelEvent::SOUND_ORB, 0, $player->getPosition()));
				}
			}

			if($item instanceof Hoe){
				$mined = $data->getBlocksMined();
				$exp = match(true){
					$mined % 1000 === 0 => mt_rand(16, 20) * 25,
					$mined % 750 === 0 => mt_rand(11, 15) * 20,
					$mined % 500 === 0 => mt_rand(6, 10) * 10,
					$mined % 250 === 0 => mt_rand(1, 5) * 5,
					default => 0
				};

				if($exp > 0){
					if(($expLvl = $data->getTreeLevel(ItemData::SKILL_EXP)) > 0){
						$exp *= ItemData::SKILL_TREES[ItemData::SKILL_EXP][$expLvl];
					}

					$exp = (int) $exp;

					$player->getXpManager()->addXp($exp);
					$player->sendTip(TextFormat::YELLOW . "You found {$exp} EXP");
				}

				$chance = ($tl = $data->getTreeLevel(ItemData::SKILL_LOOT)) * 5;

				if(mt_rand(1, 100) <= $chance){
					foreach($drops as $drop) $drop->setCount($drop->getCount() * ($tl > 3 ? 3 : 2));
				}

				$leveledUp = $data->addXp(mt_rand(2, 5));
				$data->getItem()->setLore($data->calculateLores());
				$data->send($player);

				if($leveledUp) $data->sendLevelUpTitle($player);
			}

			if(!is_null($pet)){
				$petData = $pet->getPetData();
				$buffData = array_values($petData->getBuffData());

				if(!$petData->isMaxLevel() && round(lcg_value() * 100, 2) <= 7.34){$petData->addXp(mt_rand(1, 5), $player);}

				if($petData->getIdentifier() === PetStructure::BEE){
					$upgradeChance = $buffData[0];

					if(round(lcg_value() * 100, 2) <= $upgradeChance){
						$level = -1;

						foreach(StringToItemParser::getInstance()->lookupBlockAliases($block) as $alias){
							if(($level = FarmingStructure::getLevel($alias)) != -1) break;
						}

						if($level != -1){
							if($level === FarmingStructure::MAX_LEVEL) $level--;

							$next = FarmingStructure::getNextCrop($level);

							$next = StringToItemParser::getInstance()->parse($next);

							if(!is_null($next)){
								$e->setDrops([$next]);

								$drops = $e->getDrops(); // updates the drops
							}
						}
					}

					if(count($buffData) > 1){
						$tripleChance = $buffData[1];

						if(round(lcg_value() * 100, 2) <= $tripleChance){
							foreach($drops as $drop){
								$drop->setCount($drop->getCount() + mt_rand(1, 2));
							}
						}
					}
				}
			}
		}

		if($block->asItem()->equals(VanillaBlocks::COBBLESTONE()->asItem(), false, false)){
			$mined = $data->getBlocksMined();

			if($item instanceof Pickaxe){
				$effLevel = ($item->hasEnchantment(EnchantmentRegistry::EFFICIENCY()->getEnchantment()) ? $item->getEnchantment(EnchantmentRegistry::EFFICIENCY()->getEnchantment())->getLevel() : 0);
				$blockCount = match($effLevel){
					0 => 30,
					1 => 35,
					2 => 40,
					3 => 50,
					4 => 60,
					5 => 70,
					default => 80
				};

				if($mined % $blockCount === 0){
					$essence = (round(lcg_value() * 100, 2) <= 85 ? mt_rand(1, 5) : mt_rand(5, 10));

					if ($item->hasEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())) {
						$essence += $item->getEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())->getLevel() + 1;
					}

					if(($essenceLvl = $data->getTreeLevel(ItemData::SKILL_ESSENCE)) > 0){
						$essence *= ItemData::SKILL_TREES[ItemData::SKILL_ESSENCE][$essenceLvl];
					}
					
					$essence = (int) $essence;
					$session = $player->getGameSession()->getEssence();
					$session->addEssence($essence);
					$player->sendTip(TextFormat::AQUA . "You found {$essence} Essence");
				}
			}
		}

		foreach(StringToItemParser::getInstance()->lookupBlockAliases($block) as $alias){
			if(in_array($alias, TileOreGenerator::ORES)){
				$chance = ($tl = $data->getTreeLevel(ItemData::SKILL_LOOT)) * 5;
				if(mt_rand(1, 100) <= $chance){
					foreach($drops as $drop) $drop->setCount($drop->getCount() * ($tl > 3 ? 3 : 2));
				}

				$mined = $data->getBlocksMined();

				if($item instanceof Pickaxe){
					$effLevel = ($item->hasEnchantment(EnchantmentRegistry::EFFICIENCY()->getEnchantment()) ? $item->getEnchantment(EnchantmentRegistry::EFFICIENCY()->getEnchantment())->getLevel() : 0);
					$blockCount = match($effLevel){
						0 => 30,
						1 => 35,
						2 => 40,
						3 => 50,
						4 => 60,
						5 => 70,
						default => 80
					};

					if($mined % $blockCount === 0){
						$essence = (round(lcg_value() * 100, 2) <= 85 ? mt_rand(1, 5) : mt_rand(5, 10));

						if ($item->hasEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())) {
							$essence += $item->getEnchantment(EnchantmentRegistry::TRANSMUTATION()->getEnchantment())->getLevel() + 1;
						}

						if(($essenceLvl = $data->getTreeLevel(ItemData::SKILL_ESSENCE)) > 0){
							$essence *= ItemData::SKILL_TREES[ItemData::SKILL_ESSENCE][$essenceLvl];
						}

						$essence = (int) $essence;
						$session = $player->getGameSession()->getEssence();
						$session->addEssence($essence);
						$player->sendTip(TextFormat::AQUA . "You found {$essence} Essence");
					}
				}

				// PET BUFF
				if(!is_null($pet)){
					$petData = $pet->getPetData();
					$buffData = array_values($petData->getBuffData());

					if(!$petData->isMaxLevel() && round(lcg_value() * 100, 2) <= 7.34) $petData->addXp(mt_rand(1, 5), $player);

					if($petData->getIdentifier() === PetStructure::DOG){
						$doubleChance = $buffData[0];
						$count = 0;

						if(round(lcg_value() * 100, 2) <= $doubleChance){
							$count = 2;
						}

						if(count($buffData) > 1){
							$tripleChance = $buffData[1];

							if(round(lcg_value() * 100, 2) <= $tripleChance){
								$count = 3;
							}
						}

						foreach($drops as $drop) $drop->setCount($drop->getCount() + $count);
					}elseif($petData->getIdentifier() === PetStructure::FOX){
						if(count($buffData) > 1){
							if(round(lcg_value() * 100, 2) <= $buffData[0]){
								$isession = $player->getGameSession()->getIslands();
								$island = $isession->atIsland() ? $isession->getIslandAt() : $isession->getLastIslandAt();

								if(!is_null($island)){
									$bb = new AxisAlignedBB(
										$block->getPosition()->x - 1,
										$block->getPosition()->y - 3,
										$block->getPosition()->z - 1,
										$block->getPosition()->x + 1,
										$block->getPosition()->y,
										$block->getPosition()->z + 1,
									);

									$blocks = $player->getWorld()->getCollisionBlocks($bb);

									foreach($blocks as $targetBlock){
										if(!$targetBlock instanceof OreGenerator) continue;

										foreach(StringToItemParser::getInstance()->lookupBlockAliases($block) as $alias){
											if(!isset(array_values(TileOreGenerator::BLOCKS)[$alias])) continue;

											$price = SkyBlock::getInstance()->getShops()->getValue($block->asItem(), $island->getSizeLevel()) * $buffData[1];

											$player->addTechits($price);
											$e->setDrops([]);
										}
										break;
									}
									
								}
							}
						}
					}
				}
			}
		}

		if($stsession->getSetting(SkyBlockSettings::AUTO_INV)){
			$collected = clone ($drops[0] ?? VanillaBlocks::AIR()->asItem());
			$leftover = null;
			if(count(($array = $player->getInventory()->addItem(...$drops))) > 0){
				$player->sendTip(TextFormat::RED . "Your inventory is full!");
				$player->autoDropLimit++;
				if($player->autoDropLimit < 5){
					foreach($array as $drop){
						$block->getPosition()->getWorld()->dropItem($block->getPosition(), $drop);
						if($collected->getCount() >= $drop->getCount()) $collected->pop($drop->getCount());
						$leftover = $drop;
						break;
					}
				}
			}else{
				$player->autoDropLimit = 0;
			}
			$e->setDrops([]);

			$ev = new AutoInventoryCollectEvent($player, $collected, $leftover);
			$ev->call();
		}

		if($item instanceof Axe || $item instanceof Pickaxe || $item instanceof Hoe || $item instanceof Shovel || $item instanceof Shears){
			$data->addBlocksMined();
			$data->damage($block);
			$data->send($player);

			if(
				(($item->getMaxDurability() - $item->getDamage()) / $item->getMaxDurability()) * 100 <= 45 &&
				$player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::TOOL_BREAK_ALERT)
			){
				$player->sendTip(TextFormat::EMOJI_CAUTION . TextFormat::YELLOW . " Tool has " . TextFormat::RED . ($item->getMaxDurability() - $item->getDamage()) . TextFormat::YELLOW . " durability left!");
			}
		}

		$this->plugin->getCrates()->excavate($player, $block);

		$tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
		if ($tile instanceof TileHopper) {
			/** @var Tile $tile */
			unset($this->plugin->hopperStore[$tile->getPosition()->__toString()]);
			$island->takeHopper();
			//echo "Removed hopper from hopperstore (" . $tile->getId() . ")", "\n";
		} elseif ($tile instanceof Spawner) {
			unset($this->plugin->spawnerStore[$tile->getPosition()->__toString()]);
			//echo "Removed spawner from spawnerstore (" . $tile->getId() . ")", "\n";
		}
	}

	public function onBucketFill(PlayerBucketFillEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		$block = $e->getBlockClicked();
		if ($player->atSpawn()) {
			$e->cancel();
			return;
		}

		$session = $player->getGameSession()->getIslands();
		if (!$session->atValidIsland()) {
			$e->cancel();
			return;
		}
		$island = $session->getIslandAt();
		/** @var IslandPermissions $ip */
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if (!$perm->getPermission(Permissions::EDIT_BLOCKS)) {
			$e->cancel();
			return;
		}

		if (!$island->inZone($block)) {
			$e->cancel();
			if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
				$player->sendMessage(TextFormat::RI . "Build limit reached. " . ($island->getSizeLevel() < 15 ? "This island must be at least level " . TextFormat::YELLOW . $island->getNextSizeUpgrade() . TextFormat::GRAY . " for you to build here!" : ""));
				$this->lastmsg[$player->getXuid()] = time();
			}
		}
	}

	public function onBucketEmpty(PlayerBucketEmptyEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		$block = $e->getBlockClicked();
		if ($player->atSpawn()) {
			$e->cancel();
			return;
		}

		$session = $player->getGameSession()->getIslands();
		if (!$session->atValidIsland()) {
			$e->cancel();
			return;
		}
		$island = $session->getIslandAt();
		/** @var IslandPermissions $ip */
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if (!$perm->getPermission(Permissions::EDIT_BLOCKS)) {
			$e->cancel();
			return;
		}

		if (!$island->inZone($block)) {
			$e->cancel();
			if (!isset($this->lastmsg[$player->getXuid()]) || $this->lastmsg[$player->getXuid()] != time()) {
				$player->sendMessage(TextFormat::RI . "Build limit reached. " . ($island->getSizeLevel() < 15 ? "This island must be at least level " . TextFormat::YELLOW . $island->getNextSizeUpgrade() . TextFormat::GRAY . " for you to build here!" : ""));
				$this->lastmsg[$player->getXuid()] = time();
			}
		}
	}

	public function onDrop(PlayerDropItemEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		$item = $e->getItem();
		if ($player->getGameSession()->isSaving()) {
			$e->cancel();
			return;
		}
		if (
			$player->isLoaded() &&
			$player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::NO_TOOL_DROP) &&
			$item instanceof Durable
		) {
			$e->cancel();
			$findItemSlot = function (Inventory $inv, Item $item): false|int {
				foreach ($inv->getContents(true) as $slot => $i) {
					if ($i->equalsExact($item)) return $slot;
				}
				return false;
			};
			$cursorSlot = $findItemSlot($player->getCursorInventory(), $item);
			$invSlot = $findItemSlot($player->getInventory(), $item);
			$armorSlot = $findItemSlot($player->getArmorInventory(), $item);
			$incursor = $cursorSlot !== false;
			if (!$incursor) $ininv = $invSlot !== false;
			else $ininv = false;
			if (!$ininv) $inarmor = $armorSlot !== false;
			else $inarmor = false;
			$inv = $incursor ? $player->getCursorInventory() : ($ininv ? $player->getInventory() : ($inarmor ? $player->getArmorInventory() : null));
			$session = SessionManager::registerSessionFor($player);

			$inv?->setItem($incursor ? $cursorSlot : ($inarmor ? $armorSlot : $invSlot), VanillaItems::AIR());

			$doNoGravItem = function (Item $item, AtPlayer $player): bool {
				$ie = new NoGravityItemEntity($player->getLocation(), $item, CompoundTag::create()->setString('owner', $player->getXuid()));
				$ie->setPickupDelay(5);
				$ie->setMotion(Vector3::zero());
				$ie->spawnToAll($player);
				$player->sendMessage(TextFormat::RN . "No tool drop setting failed! Your tool was dropped on your exact location.");
				return $ie->isAlive();
			};
			$doInboxAttempt = function (Item $item, AtPlayer $player) use ($doNoGravItem): bool {
				if (!$player->isLoaded()) return false;
				$inbox = $player->getSession()?->getInbox()->getInbox(1);
				if ($inbox !== null) {
					$message = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "No tool drop return", "Your tool was unable to be returned to your inventory and was thus sent here!", false);
					$message->setItems([$item]);
					$inbox->addMessage($message);
					$player->sendMessage(TextFormat::RN . "Your tool could not be returned to your inventory and was sent to your inbox! Type /inbox to access it");
				} else {
					return $doNoGravItem($item, $player);
				}
				return true;
			};
			if (!is_null($inv) && $inv->canAddItem($item) && (!$incursor || $session->isInvOpen())) {
				$inv->addItem($item);
				$player->sendTip(TextFormat::EMOJI_DENIED . TextFormat::RED . " You disabled tool dropping!");
				$player->getNetworkSession()->getInvManager()->syncAll();
			} else {
				$current = $player->getCurrentWindow();
				$chestlike = ($current instanceof ChestInventory || $current instanceof DoubleChestInventory || $current instanceof ShulkerBoxInventory);
				$containerlike = ($current instanceof CraftingTableInventory || $current instanceof PlayerCraftingInventory || $current instanceof FurnaceInventory || $current instanceof HopperInventory);
				$tempinvlike = ($current instanceof TempInventory);
				if ($chestlike || $tempinvlike) {
					if (count($current->removeItem($item)) > 0) {
						$doInboxAttempt($item, $player);
					} else {
						if (count($current->addItem($item)) > 0) {
							$doInboxAttempt($item, $player);
						} else {
							$player->sendTip(TextFormat::EMOJI_DENIED . TextFormat::RED . " You disabled tool dropping!");
							$player->getNetworkSession()->getInvManager()->syncAll();
						}
					}
				} elseif ($containerlike) {
					if (count($current->removeItem($item)) > 0) {
						$doInboxAttempt($item, $player);
					} else {
						if (count($player->getInventory()->addItem($item)) > 0) {
							$doInboxAttempt($item, $player);
						} else {
							$player->sendTip(TextFormat::EMOJI_DENIED . TextFormat::RED . " You disabled tool dropping!");
							$player->getNetworkSession()->getInvManager()->syncAll();
						}
					}
				}
			} {
			}
			return;
		}

		$lses = $player->getGameSession()->getLms();
		if ($lses->inGame()) {
			$game = $lses->getGame();
			if ($game->hasSpectator($player)) {
				$e->cancel();
				return;
			}
		}

		$session = $player->getGameSession()->getIslands();
		if (!$session->atValidIsland()) {
			return;
		}
		$island = $session->getIslandAt();
		/** @var IslandPermissions $ip */
		$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

		if (!$perm->getPermission(Permissions::DROP_ITEMS)) {
			$e->cancel();
			return;
		}
	}

	/**
	 * @priority MONITOR
	 */
	public function onEntityDamage(EntityDamageEvent $e) : void{
		if($e->isCancelled()) return;

		$entity = $e->getEntity();
		$cause = $e->getCause();

		if(
			$cause == EntityDamageEvent::CAUSE_FALL ||
			$cause == EntityDamageEvent::CAUSE_ENTITY_EXPLOSION ||
			$cause == EntityDamageEvent::CAUSE_BLOCK_EXPLOSION
		){
			$e->cancel();
			return;
		}

		if($entity instanceof SkyBlockPlayer){
			$session = $entity->getGameSession();
			if(
				!$entity->isLoaded() ||
				(!is_null($session) && $session->isSaving()) ||
				is_null($session)
			){
				$e->cancel();
				return;
			}
			$islandSession = $session->getIslands();
			$combatSession = $session->getCombat();
			
			if($islandSession->atIsland() && !$combatSession->inPvPMode()){
				$e->cancel();
				return;
			}

			$staffSession = $entity->getSession()->getStaff();

			if($e instanceof EntityDamageByEntityEvent){
				$damager = $e->getDamager();

				if($damager instanceof SkyBlockPlayer){
					if(
						!$damager->isLoaded() ||
						$damager->isVanished() || 
						(($dsession = $damager->getGameSession()) !== null && $dsession->isSaving())
					){
						$e->cancel();
						return;
					}

					$dStaffSession = $damager->getSession()->getStaff();

					if($entity->atSpawn() && !$combatSession->inPvPMode()){
						if($damager->isStaff()){
							if($dStaffSession->canPunchBack($entity)){
								$dStaffSession->punchBack($entity);
							}elseif($entity->isStaff()){
								$staffSession->punch($damager);
							}
						}else{
							$staffSession->punch($damager);
						}

						$e->cancel();
						return;
					}

					if(!$combatSession->canCombat($damager)) {
						$e->cancel();
						return;
					}

					$entity->resetCombo();
					$damager->addCombo();

					$dcm = $dsession->getCombat()->getCombatMode();
					$ecm = $combatSession->getCombatMode();
					$msg = TextFormat::RI . "You are now in combat mode! If you logout you will lose your items and 50 Techits";
	
					if(!$ecm->inCombat()) $entity->sendMessage($msg);
					if(!$dcm->inCombat()) $damager->sendMessage($msg);
	
					$ecm->setCombat($damager);
					$dcm->setCombat($entity);
					
					$this->plugin->getEnchantments()->process($e);
	
					if($entity->inFlightMode()) $entity->setFlightMode(false);
					if($damager->inFlightMode()) $damager->setFlightMode(false);
	
					if($entity->getHealth() - $e->getFinalDamage() <= 0){
						$e->cancel();
						$dsession->getCombat()->kill($entity);
					}
				}

				if($damager instanceof Mob){
					if(!$combatSession->getCombatMode()->inCombat()){
						$entity->sendMessage(TextFormat::RI . "You are now in combat mode! If you logout you will lose your items and 50 Techits");
					}

					$combatSession->getCombatMode()->setCombat($entity);
				}

				if(!$e instanceof EntityDamageByChildEntityEvent){
					$enchantmentSession = $session->getEnchantments();

					if($enchantmentSession->isAbsorbing()){
						$enchantmentSession->addAbsorbDamage($e->getFinalDamage());
						return;
					}
				}
			}elseif($e instanceof EntityDamageEvent && !$e instanceof EntityDamageByEntityEvent){
				if($entity->getHealth() - $e->getFinalDamage() <= 0){
					$e->cancel();
					$entity->getGameSession()->getCombat()->death($entity);
				}
			}
		}elseif($entity instanceof Mob){
			if($e instanceof EntityDamageByEntityEvent){
				$damager = $e->getDamager();

				if($damager instanceof SkyBlockPlayer){
					if(
						!$damager->isLoaded() || 
						(($ss = $damager->getGameSession()) !== null && $ss->isSaving())
					){
						$e->cancel();
						return;
					}

					if(!$e instanceof EntityDamageByChildEntityEvent){
						$this->plugin->getEnchantments()->process($e);
					}

					if($ss->getIslands()->atIsland()) return;

					$cm = $ss->getCombat()->getCombatMode();

					if(!$cm->inCombat()){
						$damager->sendMessage(TextFormat::RI . "You are now in combat mode! If you logout you will lose your items and 50 Techits");
					}

					$cm->setCombat($damager);

					// PET BUFF
					$session = $damager->getGameSession()->getPets();
					$pet = $session->getActivePet();

					if(!is_null($pet)){
						$petData = $pet->getPetData();
						$buffData = $petData->getBuffData();

						if($petData->getIdentifier() === PetStructure::VEX){
							$damageIncrease = $buffData[0];

							$e->setBaseDamage($e->getBaseDamage() + (($damageIncrease / 100) * $entity->getMaxHealth()));
						}
					}
				}
			}
		}elseif($entity instanceof Painting){
			if($e instanceof EntityDamageByEntityEvent){
				$damager = $e->getDamager();

				if($damager instanceof SkyBlockPlayer){
					if(
						!$damager->isLoaded() ||
						(($gs = $damager->getGameSession()) !== null && $gs->isSaving())
					){
						$e->cancel();
						return;
					}

					$isession = $gs->getIslands();
					
					if(is_null(($island = $isession->getIslandAt()))){
						$e->cancel();
						return;
					}

					$perm = $island->getPermissions()->getPermissionsBy($damager);

					if(is_null($perm)) return;

					if(!$perm->getPermission(Permissions::EDIT_BLOCKS)){
						$e->cancel();
						return;
					}
				}
			}
		}
	}

	public function onChangeLevel(EntityTeleportEvent $e) {
		$entity = $e->getEntity();
		$target = $e->getTo()->getWorld();
		if ($entity instanceof Player && $e->getFrom()->getWorld() !== $target) {
			$this->plugin->getLeaderboards()->changeLevel($entity, $target->getDisplayName());
		}
	}

	public function onItemEntSpawn(ItemSpawnEvent $e): void {
		$entity = $e->getEntity();
		$item = $entity->getItem();
		if ($item->getNamedTag()->getInt("pickup", 1) == 0) return;
		switch ($item->getTypeId()) {
			case BlockTypeIds::DIRT:
				$entity->setDespawnDelay(20 * 10);
				break;
			default:
				$entity->setDespawnDelay(20 * 30);
				break;
		}
		$entity->setPickupDelay(35);
	}

	public function onTransaction(InventoryTransactionEvent $e) {
		$t = $e->getTransaction();
		/** @var SkyBlockPlayer $player */
		$player = $t->getSource();
		if (!$player->isLoaded()) {
			$e->cancel();
			return;
		}
		if ($player->getGameSession()->isSaving()) {
			$e->cancel();
			return;
		}
		foreach ($t->getInventories() as $inventory) {
			if ($inventory instanceof ArmorInventory) {
				if ($this->plugin->getCombat()->getArenas()->inArena($player)) {
					foreach ($t->getActions() as $action) {
						if (
							$action instanceof SlotChangeAction &&
							$action->getSourceItem()->equals(ItemRegistry::ELYTRA())
						) {
							$e->cancel();
						}
					}
				}
				break;
			}
			if ($inventory instanceof BlockInventory && !$inventory instanceof CraftingTableInventory) {
				$island = $player->getGameSession()->getIslands()->getIslandAt();
				/** @var IslandPermissions $ip */
				$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
				if (!$perm->getPermission(Permissions::OPEN_CONTAINERS) && !$player->isTier3()) {
					$e->cancel();
					return;
				}
			}
			if ($inventory instanceof TrashInventory) {
				foreach ($t->getActions() as $action) {
					if (stristr($action->getTargetItem()->getCustomName(), "clearing in")) {
						$e->cancel();
					}
				}
			}
			if ($inventory instanceof TradeInventory) {
				$session = $player->getGameSession()->getTrade();
				if (!$session->isTrading()) {
					$e->cancel();
					return;
				}
				$tradesession = $session->getTradeSession();
				if ($inventory->getTradeSession() !== $tradesession) {
					$e->cancel();
					return;
				}
				foreach ($t->getActions() as $action) {
					if (!($action instanceof SlotChangeAction)) continue;

					if ($action->getInventory() instanceof TradeInventory) {
						if (in_array($action->getSlot(), $inventory->getNoTouchSlots())) {
							$e->cancel();
							continue;
						}
						if ($tradesession->getPlayer1() === $player) {
							if ($action->getSlot() == $inventory->getPlayer2ButtonSlot()) {
								$e->cancel();
								return;
							}
							if ($action->getSlot() == $inventory->getPlayer1ButtonSlot()) {
								$e->cancel();
								$inventory->toggle1();
								return;
							}
							if (!in_array($action->getSlot(), $inventory->getPlayer1ItemSlots()) || $inventory->is1Toggled()) {
								$e->cancel();
								return;
							}
							if ($inventory->is2Toggled()) $inventory->toggle2();
						} elseif ($tradesession->getPlayer2() === $player) {
							if ($action->getSlot() == $inventory->getPlayer1ButtonSlot()) {
								$e->cancel();
								return;
							}
							if ($action->getSlot() == $inventory->getPlayer2ButtonSlot()) {
								$e->cancel();
								$inventory->toggle2();
								return;
							}
							if (!in_array($action->getSlot(), $inventory->getPlayer2ItemSlots()) || $inventory->is2Toggled()) {
								$e->cancel();
								return;
							}
							if ($inventory->is1Toggled()) $inventory->toggle1();
						}
					}
				}
			}

			if(SpecialItemsInventoryHandler::handle($inventory, $player, $t)){
				$e->cancel();
			}
		}
	}

	public function onPickup(EntityItemPickupEvent $e) {
		$inventory = $e->getInventory();
		if ($inventory === null || !($inventory instanceof PlayerInventory)) return;
		$player = $inventory->getHolder();
		if ($player instanceof SkyBlockPlayer) {
			if (!$player->isLoaded()) return;

			if ($player->atSpawn()) {
				//$e->cancel();
				return;
			}

			$session = $player->getGameSession()->getIslands();
			if (!$session->atValidIsland()) {
				return;
			}
			$island = $session->getIslandAt();
			/** @var IslandPermissions $ip */
			$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

			if (!$perm->getPermission(Permissions::PICKUP_ITEMS)) {
				$e->cancel();
				return;
			}

			/*$item = $e->getItem();
			if ($item->getNamedTag()->getTag("data") !== null) {
				$e->cancel();

				$item->execute($player);
				$item->setCount(0);
				//$itementity->flagForDespawn();
			}*/
		}
	}

	public function onTrample(EntityTrampleFarmlandEvent $e) {
		$player = $e->getEntity();
		if ($player instanceof SkyBlockPlayer) {
			$session = $player->getGameSession()->getIslands();
			if (!$session->atValidIsland()) {
				$e->cancel();
				return;
			}
			$island = $session->getIslandAt();
			/** @var IslandPermissions $ip */
			$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();

			if (!$perm->getPermission(Permissions::EDIT_BLOCKS)) {
				$e->cancel();
				return;
			}
		}
	}

	public function onDataPacket(DataPacketReceiveEvent $e) {
		$packet = $e->getPacket();
		/** @var SkyBlockPlayer $player */
		$player = $e->getOrigin()->getPlayer();
		if ($packet instanceof ServerSettingsRequestPacket) {
			$player->handleServerSettingsRequest($packet);
			return;
		}

		if ($packet instanceof InventoryTransactionPacket) {
			$data = $packet->trData;
			if ($data->getTypeId() == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY && $data instanceof UseItemOnEntityTransactionData && $data->getActionType() == UseItemOnEntityTransactionData::ACTION_INTERACT) {
				$eid = $e->getOrigin()->getPlayer()?->getId() ?? -1;
				$clickPos = $data->getClickPosition();
				$slot = $data->getHotbarSlot();

				$clickPosPos = new Position($clickPos->x, $clickPos->y, $clickPos->z, $player->getWorld());

				$entity = $player->getWorld()->getEntity($eid);

				if ($entity instanceof ArmorStand) {
					if ($player->getWorld()->getDisplayName() == "scifi1") {
						$e->cancel();

						$e->getOrigin()->getInvManager()->syncContents($entity->getArmorInventory());
						#$entity->sendHandItems($player);
						return;
					}
					$session = $player->getGameSession()->getIslands();
					if (!$session->atValidIsland()) {
						$e->cancel();

						$e->getOrigin()->getInvManager()->syncContents($entity->getArmorInventory());
						#$entity->sendHandItems($player);
						return;
					}
					$island = $session->getIslandAt();
					/** @var IslandPermissions $ip */
					$perm = ($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions();
					if (!$perm->getPermission(Permissions::EDIT_ARMOR_STANDS)) {
						$e->cancel();

						$e->getOrigin()->getInvManager()->syncContents($entity->getArmorInventory());
						#$entity->sendHandItems($player);
						return;
					}
					if (!$island->inZone($clickPosPos)) {
						$e->cancel();

						$e->getOrigin()->getInvManager()->syncContents($entity->getArmorInventory());
						#$entity->sendHandItems($player);
						return;
					}
					$entity->onInteract($player, $clickPos);
					return;
				}
			}
		}
		return;
	}

	public function onCraft(CraftItemEvent $e) {
		$player = $e->getPlayer();

		$blockedIds = [
			-BlockTypeIds::STONE_PRESSURE_PLATE,
			-BlockTypeIds::WEIGHTED_PRESSURE_PLATE_HEAVY,
			-BlockTypeIds::WEIGHTED_PRESSURE_PLATE_LIGHT,
			ItemTypeIds::GOLDEN_APPLE,
			ItemTypeIds::ENCHANTED_GOLDEN_APPLE,
			// -BlockTypeIds::ARMOR_STAND, doesn't exist
			-BlockTypeIds::HOPPER,
			-BlockTypeIds::BEACON,
		];
		foreach ($e->getOutputs() as $output) {
			if (in_array($output->getTypeId(), $blockedIds)) {
				$e->cancel();
				$player->sendMessage(TextFormat::RI . "You cannot craft this item.");
				break;
			}
		}
	}

	public function onKill(SpawnerKillEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		$mob = $e->getMob();
		$player->addSpawnerKill();
		if ($player->getSpawnerKills() % 3 == 0 && !$player->isVanished()) {
			$item = $player->getInventory()->getItemInHand();
			if ($item instanceof Sword) {
				$data = new ItemData($item);
				$leveledUp = $data->addXp(mt_rand(2, 5));
				$data->getItem()->setLore($data->calculateLores());
				$data->send($player);
				if ($leveledUp) {
					$data->sendLevelUpTitle($player);
				}
			}
		}
	}

	public function onKeyFind(KeyFindEvent $e) : void{
		SkyBlock::getInstance()->getEnchantments()->process($e);
	}

	public function onChunkLoad(ChunkLoadEvent $e) {
		$world = $e->getWorld();
		if (!IslandManager::isIslandWorld($world->getDisplayName())) return;
		if (($island = $this->plugin->getIslands()->getIslandManager()->getIslandBy($world->getDisplayName())) === null) {
			return;
		}

		$chunk = $e->getChunk();
		if (isset(($tm = $island->getTextManager())->chunkIndexes[$key = $e->getChunkX() . ":" . $e->getChunkZ()])) {
			foreach ($tm->chunkIndexes[$key] as $text) {
				$text = $tm->getText($text->getCreated());
				if ($text !== null && !$text->isInitiated()) {
					$text->init();
				}
			}
		}

		foreach ($chunk->getTiles() as $tile) {
			if ($tile instanceof HopperTile) {
				/** @var Tile $tile */
				$this->plugin->hopperStore[$tile->getPosition()->__toString()] = $tile;
				//echo "Added hopper to hopperstore (" . $tile->getId() . ")", "\n";
			}
			if ($tile instanceof Spawner) {
				$this->plugin->spawnerStore[$tile->getPosition()->__toString()] = $tile;
				//echo "Added spawner to spawnerstore (" . $tile->getId() . ")", "\n";
			}
		}
	}

	public function onChunkUnload(ChunkUnloadEvent $e) {
		$world = $e->getWorld();
		if (!IslandManager::isIslandWorld($world->getDisplayName())) return;
		if (($island = $this->plugin->getIslands()->getIslandManager()->getIslandBy($world->getDisplayName())) === null) {
			return;
		}

		$chunk = $e->getChunk();
		foreach ($chunk->getTiles() as $tile) {
			if ($tile instanceof TileHopper) {
				/** @var Tile $tile */
				unset($this->plugin->hopperStore[$tile->getPosition()->__toString()]);
				//echo "Removed hopper from hopperstore (" . $tile->getId() . ")", "\n";
			}
			if ($tile instanceof Spawner) {
				/** @var Tile $tile */
				#unset($this->plugin->spawnerStore[$tile->getPosition()->__toString()]);
				//echo "Removed spawner from spawnerstore (" . $tile->getId() . ")", "\n";
			}
		}
	}

	public function onFishing(FishingEvent $e) : void{
		SkyBlock::getInstance()->getEnchantments()->process($e);
	}
}
