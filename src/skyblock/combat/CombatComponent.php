<?php

namespace skyblock\combat;

use pocketmine\Server;
use pocketmine\item\{
	Durable,
	Sword
};
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\{
	GameMode,
	Player
};

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\combat\arenas\entity\{
	MoneyBag,
	SupplyDrop
};
use skyblock\combat\utils\ModeManager;
use skyblock\enchantments\ItemData;
use skyblock\entity\ArmorStand;

use core\Core;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;
use core\discord\objects\{
	Post,
	Embed,
	Field,
	Footer,
	Webhook
};
use pocketmine\event\player\PlayerDeathEvent;

/** @method SkyBlockPlayer getPlayer() */
class CombatComponent extends SaveableComponent {

	private int $ticks = 0;

	public int $invincible = 0;

	/** @var CombatStat[] */
	public array $stats = [];

	public bool $pvpMode = false;

	public ModeManager $mode;

	public function getName(): string {
		return "combat";
	}

	public function tick(): void {
		$this->ticks++;
		if ($this->ticks % 4 !== 0) return;
		$this->getCombatMode()->tick();

		if ($this->isInvincible()) {
			if ($this->invincible - time() <= 0) {
				$this->invincible = 0;
				$this->getPlayer()?->sendMessage(TextFormat::RI . "You are no longer invincible.");
			}
		}
	}

	/**
	 * Called when this player kills another player
	 */
	public function kill(Player $player): void {
		/** @var SkyBlockPlayer $player */
		/** @var SkyBlockPlayer $killer */
		$killer = $this->getPlayer();

		$player->stopBleeding();

		$hand = $killer->getInventory()->getItemInHand();
		$item = new ItemData($hand);
		if ($hand instanceof Durable) {
			$item->addKill(true);
			//todo: check player kill limit
			$item->send($killer);
		}

		if ($item->hasEffect()) {
			$effect = $item->getEffect();
			$callable = $effect->getCallable();
			$callable($killer, $player->getPosition());
		}

		if ($player === $killer) return;

		$lsession = $killer->getGameSession()->getLms();
		if ($lms = $lsession->inGame()) {
			$lsession->addKill();

			$weaponTxt = $hand->getVanillaName() . ($hand->hasCustomName() ? " (" . $hand->getCustomName() . ")" : "");
			$ench = $item->getEnchantments();
			if (($cnt = count($ench)) > 0) {
				$weaponTxt .= PHP_EOL . "(" . $cnt . " enchantments: " . PHP_EOL;
				foreach ($ench as $en) {
					$weaponTxt .= $en->getName() . " " . $en->getStoredLevel() . PHP_EOL;
				}
				$weaponTxt = rtrim($weaponTxt);
				$weaponTxt .= ")";
			}
			$post = new Post("", "LMS Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $killer->getName() . "** just killed **" . $player->getName() . "**", "", "ffb106", new Footer("Vlumpkin | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
					new Field($killer->getName() . "'s weapon", $weaponTxt, true),
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("lms-log"));
			$post->send();
		}

		$session = $player->getGameSession()->getCombat();
		$session->death($killer);

		$killer->addTechits(2);

		$arenas = SkyBlock::getInstance()->getCombat()->getArenas();
		if ($arenas->inArena($killer)) {
			$this->addKill();
			$arenas->getArena()->addStreak($killer);
		}

		$koth = SkyBlock::getInstance()->getKoth();
		$ksession = $killer->getGameSession()->getKoth();
		if ($koth->inGame($killer)) {
			$ksession->addKill();
		}

		Core::announceToSS(TextFormat::LIGHT_PURPLE . $player->getName() . TextFormat::RED . " was " . ($item->hasDeathMessage() ? $item->getDeathMessage() . TextFormat::RESET . TextFormat::RED : "killed") . " by " . TextFormat::LIGHT_PURPLE . $killer->getName() . "'s " . TextFormat::DARK_PURPLE . "[" . ($hand->isNull() ? "Fists" : $item->getName()) . TextFormat::RESET . TextFormat::DARK_PURPLE . "]");
	}

	/**
	 * Called when player kills a mob lol
	 */
	public function maul(Entity $entity): void {
	}

	/**
	 * Called when player kills this player
	 */
	public function death(Player $player): void {
		/** @var SkyBlockPlayer $player */
		/** @var SkyBlockPlayer $dead */
		$dead = $this->getPlayer();

		(new PlayerDeathEvent($dead, [], 0, null))->call();

		$arenas = SkyBlock::getInstance()->getCombat()->getArenas();
		if ($arenas->inArena($dead)) {
			$this->addDeath();
		}

		$ksession = $dead->getGameSession()->getKoth();
		if ($ksession->inGame()) {
			$ksession->addDeath();

			$ksession->setGame();
		}

		$lsession = $dead->getGameSession()->getLms();
		if ($lms = $lsession->inGame()) {
			$lsession->addDeath();
		}

		if (($pl = $this->getPlayer()) !== null) {
			if (!$lms) {
				$this->reset(true, !$ai = ($is = $pl->getGameSession()->getIslands())->atIsland());

				if ($ai) {
					$is->getIslandAt()->teleportTo($pl);
					return;
				}
				/** @var SkyBlockPlayer $pl */
				$pl->gotoSpawn(TextFormat::RI . "You died! Cringe");
			} else {
				$game = $lsession->getGame();
				$game->removePlayer($dead);
				$game->addSpectator($dead);
				if (count($game->getPlayers()) === 1) {
					$game->winner = $player;
					$game->end(true);
				} else {
					$dead->sendMessage(TextFormat::RI . "You died! You are now in spectator mode.");
				}
			}
		}
	}

	/**
	 * Called when player dies to mob
	 */
	public function ded(Entity $entity): void {
		$this->reset(true);

		if (($pl = $this->getPlayer()) !== null) {
			/** @var SkyBlockPlayer $pl */
			$pl->gotoSpawn(TextFormat::RI . "You died! Cringe");
		}
	}

	public function suicide(): void {
		$this->reset(true);

		if (($pl = $this->getPlayer()) !== null) {
			/** @var SkyBlockPlayer $pl */
			$pl->gotoSpawn(TextFormat::RI . "You died! Cringe");
		}
	}

	public function canCombat(?Entity $entity = null): bool {
		if (is_null($entity) || !$entity->isAlive()) return false;
		if ($entity instanceof SupplyDrop || $entity instanceof MoneyBag || $entity instanceof ArmorStand) return true;
		$player = $this->getPlayer();
		if (!$player instanceof Player) return false;
		if ($player->isVanished()) return false;
		if ($player->isFrozen()) return false;

		if ($this->isInvincible()) return false;
		if ($player->getGameSession()->getKoth()->inGame()) return true;
		if (($lms = $player->getGameSession()->getLms())->inGame()) {
			$game = $lms->getGame();
			return $game->isStarted() && $game->hasPlayer($player);
		}
		if (SkyBlock::getInstance()->getCombat()->getArenas()->inArena($player)) return true;

		if ($this->inPvPMode()) {
			if (($entity instanceof SkyBlockPlayer && $entity->isConnected() && $entity->getGameSession()->getCombat()->inPvPMode()) || (
				$entity instanceof EntityDamageByEntityEvent &&
				$entity->getDamager() instanceof SkyBlockPlayer &&
				$entity->getDamager()->getGameSession()->getCombat()->inPvPMode()
			)) {
				return true;
			}
		}

		$isession = $player->getGameSession()->getIslands();
		if ($isession->atIsland()) {
			if ($entity instanceof Player) {
				return false;
			}
			return true; //mob spawner entities can kill
		}
		return false;
	}

	/**
	 * Resets player, teleports back to spawn.
	 */
	public function reset(bool $dropitems = false, bool $icheck = true): void {
		$player = $this->getPlayer();
		$player->stopBleeding();
		$player->getEffects()->clear();
		$player->setHealth(20);
		$player->getHungerManager()->setFood(20);
		$player->getHungerManager()->setSaturation(20);
		$player->extinguish();

		if ($dropitems) {
			$player->getWorld()->dropExperience($player->getPosition(), $player->getXpDropAmount());

			foreach ($player->getInventory()->getContents() as $item) {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach ($player->getArmorInventory()->getContents() as $item) {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach ($player->getCursorInventory()->getContents() as $item) {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach ($player->getOffhandInventory()->getContents() as $item) {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach ($player->getCraftingGrid()->getContents() as $item) {
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
		}
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getOffhandInventory()->clearAll();
		$player->getCraftingGrid()->clearAll();
		$player->getXpManager()->setCurrentTotalXp(0);

		if ($dropitems) {
			$player->getGameSession()->getData()->update();
		}

		if ($icheck) {
			$isession = $player->getGameSession()->getIslands();
			if ($isession->atIsland()) {
				$isession->setIslandAt();
			}

			$player->teleport(SkyBlock::getInstance()->getSpawnPosition(), 90, 0);
		}

		$this->getCombatMode()->reset(false);

		$player->setAllowFlight(true);

		if ($this->inPvPMode()) {
			$this->togglePvPMode();
		}
	}

	public function isInvincible(): bool {
		return $this->invincible - time() >= 0;
	}

	public function setInvincible(int $seconds = 5): void {
		$this->invincible = time() + $seconds;
		$this->getPlayer()->sendMessage(TextFormat::YI . "You have invincibility for " . TextFormat::YELLOW . $seconds . TextFormat::GRAY . " seconds.");
	}

	public function getStats(int $type = CombatStat::TYPE_ALLTIME): ?CombatStat {
		return $this->stats[$type] ?? null;
	}

	public function getKills(int $type = CombatStat::TYPE_ALLTIME): int {
		return $this->stats[$type]?->getKills() ?? 0;
	}

	public function addKill(): void {
		foreach ($this->stats as $stat) {
			$stat->addKill();
		}
	}

	public function getDeaths(int $type = CombatStat::TYPE_ALLTIME): int {
		return $this->stats[$type]?->getDeaths() ?? 0;
	}

	public function addDeath(): void {
		foreach ($this->stats as $stat) {
			$stat->addDeath();
		}
	}

	public function getSupplyDrops(int $type = CombatStat::TYPE_ALLTIME): int {
		return $this->stats[$type]?->getSupplyDrops() ?? 0;
	}

	public function addSupplyDrop(): void {
		foreach ($this->stats as $stat) {
			$stat->addSupplyDrop();
		}
	}

	public function getMoneyBags(int $type = CombatStat::TYPE_ALLTIME): int {
		return $this->stats[$type]?->getMoneyBags() ?? 0;
	}

	public function addMoneyBag(): void {
		foreach ($this->stats as $stat) {
			$stat->addMoneyBag();
		}
	}

	public function getMobs(int $type = CombatStat::TYPE_ALLTIME): int {
		return $this->stats[$type]?->getMobs() ?? 0;
	}

	public function addMob(): void {
		foreach ($this->stats as $stat) {
			$stat->addMob();
		}
	}

	public function inPvPMode(): bool {
		return $this->pvpMode;
	}

	public function togglePvPMode(): void {
		$this->pvpMode = !$this->pvpMode;
		$player = $this->getPlayer();
		if (!$player instanceof Player) return;
		if (!$this->inPvPMode()) {
			if (!SkyBlock::getInstance()->getCombat()->getArenas()->inArena($player) && !$player->getGameSession()->getKoth()->inGame()) {
				$player->setAllowFlight(true);
			}
		} else {
			$player->setAllowFlight(false);
			$player->setGamemode(GameMode::CREATIVE());
			$player->setGamemode($player->atSpawn() ? GameMode::ADVENTURE() : GameMode::SURVIVAL());

			if ($player->inFlightMode()) $player->setFlightMode(false);
		}

		$player->updateNametag();
		$player->updateChatFormat();
	}

	public function getCombatMode(): ?ModeManager {
		return $this->mode;
	}

	public function inCombat(): bool {
		return $this->getCombatMode()->inCombat();
	}

	public function resetStats(int $type = CombatStat::TYPE_WEEKLY): void {
		$stat = $this->getStats($type);
		if ($stat !== null) {
			$stat->setKills(0);
			$stat->setDeaths(0);
			$stat->setSupplyDrops(0);
			$stat->setMoneyBags(0);
			$stat->setMobs(0);
		}
	}

	public function delete(): void {
		foreach ($this->stats as $stat) {
			$stat->setKills(0);
			$stat->setDeaths(0);
			$stat->setSupplyDrops(0);
			$stat->setMoneyBags(0);
			$stat->setMobs(0);
		}
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach (
			[
				//"DROP TABLE IF EXISTS combat_stats",
				"CREATE TABLE IF NOT EXISTS combat_stats(
				xuid BIGINT(16) NOT NULL,
				ttype INT NOT NULL,
				kills INT NOT NULL DEFAULT 0, deaths INT NOT NULL DEFAULT 0,
				supply_drops INT NOT NULL DEFAULT 0, money_bags INT NOT NULL DEFAULT 0,
				mobs INT NOT NULL DEFAULT 0,
				PRIMARY KEY(xuid, ttype)
			)",
			] as $query
		) $db->query($query);
	}

	public function loadAsync(): void {
		$this->mode = new ModeManager($this);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM combat_stats WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = $result->getRows();
		foreach ($rows as $row) {
			$stats = new CombatStat(
				$this,
				$type = $row["ttype"],
				$row["kills"],
				$row["deaths"],
				$row["supply_drops"],
				$row["money_bags"],
				$row["mobs"]
			);
			$this->stats[$type] = $stats;
		}
		for ($i = 0; $i <= 2; $i++) {
			if (!isset($this->stats[$i])) {
				$this->stats[$i] = new CombatStat($this, $i);
			}
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange(): bool {
		$verify = $this->getChangeVerify();
		return
			$this->getStats()->getKills() !== $verify["kills"] ||
			$this->getStats()->getDeaths() !== $verify["deaths"] ||
			$this->getStats()->getSupplyDrops() !== $verify["drops"] ||
			$this->getStats()->getMoneyBags() !== $verify["bags"] ||
			$this->getStats()->getMobs() !== $verify["mobs"];
	}

	public function saveAsync(): void {
		if (!$this->isLoaded()) return;

		$this->setChangeVerify([
			"kills" => $this->getStats()->getKills(),
			"deaths" => $this->getStats()->getDeaths(),
			"drops" => $this->getStats()->getSupplyDrops(),
			"bags" => $this->getStats()->getMoneyBags(),
			"mobs" => $this->getStats()->getMobs(),
		]);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), []);
		foreach ($this->stats as $stat) {
			if ($stat->hasChanged()) {
				$request->addQuery($stat->getQuery($this->getUser()));
				$stat->setChanged(false);
			}
		}
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function finishSaveAsync(): void {
		parent::finishSaveAsync();
		//todo: check if stats changed during save process?
	}

	public function save(): bool {
		if (!$this->isLoaded()) return false;

		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ($this->stats as $stat) {
			if ($stat->hasChanged()) {
				$stat->mtSave($this->getUser(), $db);
				$stat->setChanged(false);
			}
		}

		return parent::save();
	}

	public function getSerializedData(): array {
		$stats = [];
		foreach ($this->stats as $stat) {
			$stats[] = [
				"ttype" => $stat->type,
				"kills" => $stat->getKills(),
				"deaths" => $stat->getDeaths(),
				"supply_drops" => $stat->getSupplyDrops(),
				"money_bags" => $stat->getMoneyBags(),
				"mobs" => $stat->getMobs(),
			];
		}
		return [
			"stats" => $stats
		];
	}

	public function applySerializedData(array $data): void {
		foreach ($data["stats"] as $stat) {
			$stats = new CombatStat(
				$this,
				$type = $stat["ttype"],
				$stat["kills"],
				$stat["deaths"],
				$stat["supply_drops"],
				$stat["money_bags"],
				$stat["mobs"]
			);
			$this->stats[$type] = $stats;
		}
		for ($i = 0; $i <= 2; $i++) {
			if (!isset($this->stats[$i])) {
				$this->stats[$i] = new CombatStat($this, $i);
			}
		}
	}
}
