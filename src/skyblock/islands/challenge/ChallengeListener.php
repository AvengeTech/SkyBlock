<?php namespace skyblock\islands\challenge;

use pocketmine\player\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerInteractEvent,
	PlayerExperienceChangeEvent,
	PlayerJumpEvent,
    PlayerToggleSneakEvent
};
use pocketmine\event\block\{
	BlockPlaceEvent,
	BlockBreakEvent
};
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\inventory\PlayerInventory;
use skyblock\SkyBlock;
use skyblock\islands\challenge\ChallengeData as CD;

use skyblock\generators\event\GeneratorUpgradeEvent;
use skyblock\islands\event\IslandUpgradeEvent;
use skyblock\islands\permission\Permissions;
use skyblock\shop\event\{
	ShopBuyEvent,
	ShopSellEvent
};
use skyblock\spawners\event\{
	SpawnerKillEvent,
	SpawnerUpgradeEvent
};
use skyblock\event\AutoInventoryCollectEvent;
use skyblock\crates\event\{
	KeyGiveEvent
};
use skyblock\enchantments\event\ApplyEnchantmentEvent;
use skyblock\enchantments\event\RefineEssenceEvent;
use skyblock\enchantments\event\RepairItemEvent;
use skyblock\fishing\event\FishingEvent;
use skyblock\generators\event\GeneratorApplyItemEvent;
use skyblock\pets\event\PetEvent;
use skyblock\pets\event\UnlockPetBoxEvent;
use skyblock\SkyBlockPlayer;

class ChallengeListener implements Listener{

	private array $tapCooldown = [];

	public function __construct(
		private SkyBlock $plugin, public Challenges $challenges
	){}

	public function setTapCooldown(Player $player) : self{
		$this->tapCooldown[$player->getXuid()] = time();

		return $this;
	}

	public function hasTapCooldown(Player $player) : bool{
		return isset($this->tapCooldown[$player->getXuid()]) && $this->tapCooldown[$player->getXuid()] == time();
	}

	public function onInteract(PlayerInteractEvent $e){
		if($e->isCancelled()) return;
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if($e->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK && !$this->hasTapCooldown($player)){
			SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
				CD::BONEMEAL_SAPLINGS,
				CD::GROW_BIRCH_SAPLINGS
			]);
			$this->setTapCooldown($player);
		}
	}

	public function onExpChange(PlayerExperienceChangeEvent $e){
		if($e->isCancelled()) return;
		/** @var SkyBlockPlayer $player */
		$player = $e->getEntity();
		if($player instanceof Player){
			if(
				!$player->isLoaded() ||
				($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
				!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
			) return;

			SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
				CD::LEVEL_UP
			]);
		}
	}

	public function onJump(PlayerJumpEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::JUMP_1,
			CD::JUMP_2
		]);
	}

	public function onPlace(BlockPlaceEvent $e){
		if($e->isCancelled()) return;
		$player = $e->getPlayer();
		/** @var SkyBlockPlayer $player */
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::PLANT_SUGARCANE,
			CD::PLANT_CACTUS,
			CD::PLANT_WHEAT,
			CD::PLANT_OAK_SAPLING,
			CD::PLANT_JUNGLE_SAPLINGS,
			CD::PLACE_VINES,
			CD::PLANT_BEETROOT,
			CD::PLANT_ACACIA_SAPLINGS,
			CD::PLANT_PUMPKINS,
			CD::PLANT_MELON,
			CD::PLANT_DARK_OAK_SAPLINGS,
			CD::PLANT_NETHER_WART,
			CD::PLANT_BAMBOO,
			CD::PLANT_RED_MUSHROOM,
			CD::PLANT_BROWN_MUSHROOM,
			CD::PLANT_CHORUS_FRUIT
		]);
	}

	public function onBreak(BlockBreakEvent $e){
		if($e->isCancelled()) return;
		$player = $e->getPlayer();
		/** @var SkyBlockPlayer $player */
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::BREAK_WOOD_1,
			CD::BREAK_WOOD_2,
			CD::MINE_COBBLESTONE_1,
			CD::MINE_COBBLESTONE_2,
			CD::MINE_OBSIDIAN_1,
			CD::MINE_GLOWING_OBSIDIAN_1,
			CD::MINE_ANCIENT_DEBRIS_1,
			CD::MINE_GILDED_OBSIDIAN_1,
			CD::MINE_ANCIENT_DEBRIS_2
		]);
	}

	public function onPickup(EntityItemPickupEvent $e){
		if($e->isCancelled()) return;
		/** @var PlayerInventory $inventory */
		$inventory = $e->getInventory();
		if($inventory === null) return;
		/** @var SkyBlockPlayer $player */
		$player = $inventory->getHolder();

		if($player instanceof Player){
			if(
				!$player->isLoaded() ||
				($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
				!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
			) return;

			SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
				CD::COLLECT_SUGARCANE,
				CD::COLLECT_CACTUS,
				CD::COLLECT_WHEAT,
				CD::COLLECT_SPRUCE_1,
				CD::COLLECT_POTATOES,
				CD::COLLECT_SPRUCE_2,
				CD::COLLECT_COAL,
				CD::COLLECT_JUNGLE_1,
				CD::COLLECT_JUNGLE_2,
				CD::COLLECT_ACACIA_1,
				CD::COLLECT_ACACIA_2,
				CD::COLLECT_DARK_OAK_1,
				CD::COLLECT_LEAVES,
				CD::COLLECT_PUMPKINS,
				CD::COLLECT_DARK_OAK_2,
				CD::COLLECT_ROTTEN_FLESH,
				CD::COLLECT_EMERALD,
				CD::COLLECT_PRISMARINE_SHARDS,
				CD::COLLECT_WITHER_SKULL
			]);
		}
	}

	public function onAutoPickup(AutoInventoryCollectEvent $e){
		$player = $e->getPlayer();
		$item = $e->getItem();

		/** @var SkyBlockPlayer $player */
		if($player instanceof Player){
			if(
				!$player->isLoaded() ||
				($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
				!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
			) return;
			SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
				CD::COLLECT_SUGARCANE,
				CD::COLLECT_CACTUS,
				CD::COLLECT_WHEAT,
				CD::COLLECT_SPRUCE_1,
				CD::COLLECT_POTATOES,
				CD::COLLECT_SPRUCE_2,
				CD::COLLECT_COAL,
				CD::COLLECT_JUNGLE_1,
				CD::COLLECT_JUNGLE_2,
				CD::COLLECT_ACACIA_1,
				CD::COLLECT_ACACIA_2,
				CD::COLLECT_DARK_OAK_1,
				CD::COLLECT_LEAVES,
				CD::COLLECT_PUMPKINS,
				CD::COLLECT_DARK_OAK_2,
				CD::COLLECT_ROTTEN_FLESH,
				CD::COLLECT_EMERALD,
				CD::COLLECT_PRISMARINE_SHARDS,
				CD::COLLECT_WITHER_SKULL
			]);
		}
	}

	public function onCraft(CraftItemEvent $e){
		if($e->isCancelled()) return;

		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::FURNACE_CRAFT,
			CD::BED_CRAFT,
			CD::TRAPDOOR_CRAFT,
			CD::COBBLESTONE_SLAB_CRAFT,
			CD::COBBLESTONE_OAK_STAIR,
			CD::BUTTON_CRAFT,
			CD::CRAFT_BRICKS,
			CD::CRAFT_PANES,
			CD::CRAFT_COAL_BLOCKS,
			CD::CRAFT_CHEST,
			CD::CRAFT_FENCE,
			CD::CRAFT_GATE,
			CD::CRAFT_TORCH,
			CD::CRAFT_SIGN,
			CD::CRAFT_GREEN_WOOL,
			CD::CRAFT_IRON_BLOCKS,
			CD::CRAFT_IRON_NUGGETS,
			CD::CRAFT_BREAD,
			CD::CRAFT_STONE_BRICKS,
			CD::CRAFT_WHITE_WOOL,
			CD::CRAFT_PAINTINGS,
			CD::CRAFT_LADDERS,
			CD::CRAFT_BOW,
			CD::CRAFT_POLISHED_GRANITE,
			CD::CRAFT_REDSTONE_BLOCKS,
			CD::CRAFT_POLISHED_DIORITE,
			CD::CRAFT_CARPET,
			CD::CRAFT_BEETROOT_SOUP,
			CD::CRAFT_LAPIS_BLOCKS,
			CD::CRAFT_SNOW_BLOCKS,
			CD::CRAFT_GOLD_NUGGETS,
			CD::CRAFT_DIAMOND_BLOCKS_1,
			CD::CRAFT_EMERALD_BLOCKS_1,
			CD::CRAFT_CLOCK,
		]);
	}

	public function onFish(FishingEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::COLLECT_FISH_1,
			CD::COLLECT_FISH_2,
			CD::COLLECT_FISH_3,
			CD::COLLECT_FISH_4,
			CD::COLLECT_FISH_5,
			CD::COLLECT_FISH_6,
			CD::LAVA_FISHING_1,
			CD::LAVA_FISHING_2,
			CD::LAVA_FISHING_3
		]);
	}

	public function onGenUpgrade(GeneratorUpgradeEvent $e){
		$player = $e->getPlayer();
		/** @var SkyBlockPlayer $player */
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::UPGRADE_COAL_GEN_1,
			CD::UPGRADE_IRON_GEN_1,
			CD::UPGRADE_REDSTONE_GEN_1,
			CD::UPGRADE_LAPIS_GEN,
			CD::UPGRADE_COPPER_GEN_1,
			CD::UPGRADE_DIAMOND_GEN_1,
			CD::UPGRADE_EMERALD_GEN_1,
			CD::UPGRADE_OBSIDIAN_GEN_1,
			CD::UPGRADE_DIAMOND_GEN_2,
			CD::UPGRADE_GLOWING_OBSIDIAN_GEN_1,
			CD::UPGRADE_EMERALD_ORE_GEN_2,
			CD::UPGRADE_GLOWING_OBSIDIAN_GEN_2,
			CD::UPGRADE_ANCIENT_DEBRIS_1,
			CD::UPGRADE_GILDED_OBSIDIAN_1,
			CD::UPGRADE_ANCIENT_DEBRIS_2,
			CD::UPGRADE_GILDED_OBSIDIAN_2
		]);
	}

	public function onBuy(ShopBuyEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::BUY_COAL_GEN,
			CD::BUY_IRON_GEN,
			CD::BUY_REDSTONE_GEN,
			CD::BUY_LAPIS_GEN,
			CD::BUY_QUARTZ_BLOCK,
			CD::BUY_NETHERBRICK_BLOCK,
			CD::BUY_COPPER_GEN,
			CD::BUY_BOOKSHELVES,
			CD::BUY_GOLD_GEN,
			CD::BUY_END_STONE,
			CD::BUY_AUTOMINER,
			CD::BUY_DIAMOND_GEN,
			CD::BUY_WHITE_STAINED_GLASS,
			CD::BUY_EMERALD_GEN,
			CD::BUY_PURPUR_BLOCKS,
			CD::BUY_OBSIDIAN_GEN,
			CD::BUY_PURPUR_QUARTZ_STONE_BRICK,
			CD::BUY_BLACK_WOOL_CONCRETE,
			CD::BUY_GLOWING_OBSIDIAN_GEN,
			CD::BUY_MAGMA,
			CD::BUY_DIMENSIONAL,
			CD::BUY_ELYTRA,
			CD::BUY_ARMOR_STAND,
		]);
	}

	public function onSell(ShopSellEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::SELL_CARROTS,
			CD::SELL_COPPER_BLOCKS,
			CD::SELL_MELON,
			CD::SELL_GOLD_BLOCKS,
			CD::SELL_NETHER_WART,
			CD::SELL_DIAMONDS,
			CD::SELL_BAMBOO,
			CD::SELL_RED_MUSHROOM,
			CD::SELL_BROWN_MUSHROOM,
			CD::SELL_CHORUS_FRUIT
		]);
	}

	public function onKillSpawner(SpawnerKillEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::KILL_PIGS_1,
			CD::KILL_PIGS_2,
			CD::KILL_CHICKENS_1,
			CD::KILL_CHICKENS_2,
			CD::KILL_SHEEP_1,
			CD::KILL_SHEEP_2,
			CD::KILL_COWS_1,
			CD::KILL_COWS_2,
			CD::KILL_SPIDERS_1,
			CD::KILL_SPIDERS_2,
			CD::KILL_SKELETONS_1,
			CD::KILL_SKELETONS_2,
			CD::KILL_ZOMBIES_1,
			CD::KILL_ZOMBIES_2,
			CD::KILL_CREEPERS_1,
			CD::KILL_CREEPERS_2,
			CD::KILL_MOOSHROOM_1,
			CD::KILL_MOOSHROOM_2,
			CD::KILL_BLAZES_1,
			CD::KILL_BLAZES_2,
			CD::KILL_BREEZES_1,
			CD::KILL_BREEZES_2,
			CD::KILL_ENDERMEN_1,
			CD::KILL_ENDERMEN_2,
			CD::KILL_WITCH_1,
			CD::KILL_WITCH_2,
			CD::KILL_GOLEM_1,
			CD::KILL_GOLEM_2,
		]);
	}

	public function onIslandUpgrade(IslandUpgradeEvent $e){
		$player = $e->getPlayer();
		$island = $e->getIsland();

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::ISLAND_EXPAND
		]);
	}

	public function onSpawnerUpgrade(SpawnerUpgradeEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::UPGRADE_MOB_SPAWNER_1,
			CD::UPGRADE_MOB_SPAWNER_2,
			CD::UPGRADE_MOB_SPAWNER_3,
			CD::UPGRADE_MOB_SPAWNER_4,
			CD::UPGRADE_MOB_SPAWNER_5
		]);
	}

	public function onKeyGive(KeyGiveEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if($player === null) return;
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if($player instanceof Player){
			if(!$player->isLoaded()) return;

			SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
				CD::COLLECT_IRON_KEYS_1,
				CD::COLLECT_IRON_KEYS_2,
				CD::COLLECT_GOLD_KEYS_1,
				CD::COLLECT_GOLD_KEYS_2,
				CD::COLLECT_DIAMOND_KEYS_1,
				CD::COLLECT_DIAMOND_KEYS_2,
				CD::COLLECT_EMERALD_KEYS_1,
				CD::COLLECT_EMERALD_KEYS_2
			]);
		}
	}

	public function onRepairItem(RepairItemEvent $e) {
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();
		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::REPAIR_ITEM
		]);
	}

	public function onEnchant(ApplyEnchantmentEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if(!$player->isLoaded()) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::APPLY_ENCHANTMENT_1,
			CD::APPLY_ENCHANTMENT_2,
			CD::APPLY_ENCHANTMENT_3,
			CD::APPLY_ENCHANTMENT_4,
			CD::APPLY_ENCHANTMENT_5
		]);
	}

	public function onGenApply(GeneratorApplyItemEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if(!$player->isLoaded()) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::APPLY_EXTENDER,
			CD::APPLY_SOLIDIFIER
		]);
	}

	public function onRefine(RefineEssenceEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = ($player->getGameSession()->getIslands()->getIslandAt() ?? $player->getGameSession()->getIslands()->getLastIslandAt())) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if(!$player->isLoaded()) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::REFINE_ESSENCE_1,
			CD::REFINE_ESSENCE_2,
			CD::REFINE_ESSENCE_3,
			CD::REFINE_ESSENCE_4,
			CD::REFINE_ESSENCE_5
		]);
	}

	public function onPetEvent(PetEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if(!$player->isLoaded()) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::PET_LEVEL_UP_1,
			CD::PET_LEVEL_UP_2,
			CD::GAIN_PET_XP_1,
			CD::GAIN_PET_XP_2,
		]);
	}

	public function onBoxEvent(UnlockPetBoxEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if(!$player->isLoaded()) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::UNLOCK_PET_BOX
		]);
	}

	public function onSn3ak(PlayerToggleSneakEvent $e){
		/** @var SkyBlockPlayer $player */
		$player = $e->getPlayer();

		if(
			!$player->isLoaded() ||
			($island = $player->getGameSession()->getIslands()->getIslandAt()) === null ||
			!(($ip = $island->getPermissions())->getPermissionsBy($player) ?? $ip->getDefaultVisitorPermissions())->getPermission(Permissions::COMPLETE_CHALLENGES)
		) return;

		if(!$player->isLoaded() || !$player->isSneaking()) return;

		SkyBlock::getInstance()->getIslands()->getChallenges()->process($island, $e, $player, [
			CD::SNEAK_1,
			CD::SNEAK_2
		]);
	}
}