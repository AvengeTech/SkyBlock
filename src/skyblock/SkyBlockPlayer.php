<?php namespace skyblock;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\{
	ServerSettingsRequestPacket,
};
use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\world\particle\BlockBreakParticle;

use skyblock\{
	SkyBlock,

	islands\permission\Permissions,
};

use core\Core;
use core\AtPlayer;
use core\network\Links;
use core\network\protocol\PlayerLoadActionPacket;
use core\network\server\ServerInstance;
use core\network\server\SubServer;
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;
use pocketmine\block\VanillaBlocks;
use skyblock\inventory\EnchantmentInventoryListener;
use skyblock\inventory\EnderchestInventory;

class SkyBlockPlayer extends AtPlayer{

	public ?SkyBlockPlayer $bleedInflict = null;
	public int $bleedTicks = 0;

	public bool $healthChanged = false;

	public int $autoDropLimit = 0;

	public bool $genMode = false;
	public $gappleCooldown = 0;

	public bool $loadedSkyBlock = false;

	public int $spawnerKills = 0;

	public int $lastPressurePlateActivation = 0;

	public bool $networkPropertiesDirty = false;
	
	public bool $elytra = false;
	
	public int $combo = 0;
	public int $comboTicks = -1;

	public EnderchestInventory $enderChest;

	public function __construct(...$args)
	{
		parent::__construct(...$args);

		$this->armorInventory->getListeners()->add(new EnchantmentInventoryListener($this));

		$this->enderChest = new EnderchestInventory($this);
	}

	public function getEnderChest(): EnderchestInventory {
		return $this->enderChest;
	}

	public function getGameSession() : ?SkyBlockSession{
		return SkyBlock::getInstance()->getSessionManager()->getSession($this);
	}

	public function hasGameSession() : bool{
		return $this->getGameSession() !== null;
	}

	public function getTechits() : int{
		return $this->getGameSession()->getTechits()->getTechits();
	}

	public function setTechits(int $value) : void{
		$this->getGameSession()->getTechits()->setTechits($value);
	}

	public function takeTechits(int $value) : void{
		$this->getGameSession()->getTechits()->takeTechits($value);
	}

	public function addTechits(int $value) : void{
		$this->getGameSession()->getTechits()->addTechits($value);
	}

	protected function onDeath() : void{
		parent::onDeath();

		if($this->isBleeding()){
			$this->stopBleeding();
		}
	}

	public function onUpdate(int $currentTick) : bool{
		parent::onUpdate($currentTick);

		if ($this->spawned && $this->getGameSession()?->getPets()->isLoaded() && ($id = $this->getGameSession()?->getPets()->needsLoad) > -1) {
			$this->getGameSession()->getPets()->loadPet($id, $this);
		}

		$combat = $this->getGameSession()?->getCombat()->getCombatMode()?->inCombat() ?? false;
		$this->setScoreTag($this->getHealthBar(null, $combat));
		
		if($this->getCombo() > 0){
			if($this->comboTicks >= 0){
				$this->comboTicks--;
			}else{
				$this->combo = 0;
			}
		}

		if($this->bleedTicks > 0){
			$this->bleedTicks--;
			$session = $this->getGameSession()?->getCombat();
			if ($this->bleedInflict instanceof SkyBlockPlayer && !is_null($session)) {
				if($this->bleedTicks % 15 == 0){
					$damage = mt_rand(1, 2);
					if ($this->getHealth() <= $damage) {
						if ($session->canCombat($this->bleedInflict) && $this->bleedInflict->isAlive() && $this->bleedInflict->isConnected()) {
							if (is_null($this->bleedInflict->getGameSession()?->getCombat()->kill($this))) $session->suicide();
						}else{
							$session->suicide();
						}
					} else {
						if ($this->getGameSession()?->getEnchantments()->isAbsorbing()) {
							$this->getGameSession()->getEnchantments()->addAbsorbDamage($damage);
						}

						$this->setHealth($this->getHealth() - $damage);
						$this->getWorld()->addParticle($this->getPosition(), new BlockBreakParticle(VanillaBlocks::REDSTONE()));
					}
				}
			}

			if($this->bleedTicks == 0){
				$this->bleedInflict = null;
			}
		}

		if (($session = $this->getGameSession()?->getIslands())?->atValidIsland() && !is_null($perm = $session?->getIslandAt()?->getPermissions()->getPermissionsBy($this))) {
			$this->getXpManager()->setCanAttractXpOrbs($perm->getPermission(Permissions::PICKUP_XP));
		} else {
			$this->getXpManager()->setCanAttractXpOrbs(true);
		}

		return $this->isAlive();
	}

	public function getHealthBar(?SkyBlockPlayer $for = null, bool $combat = false): string {
		$max = round($this->getMaxHealth());
		$health = round($this->getHealth());
		$absorption = round($this->getAbsorption());
		$inputMode = $this->getInputMode();
		$os = $this->getDeviceOSname();
		if ($combat) return TextFormat::GREEN . $inputMode . TextFormat::AQUA . " [" . ($health >= 0 ? TextFormat::GREEN . str_repeat("|", (int) $health) : "") . (($max - $health) >= 0 ? TextFormat::RED . str_repeat("|", (int) ($max - $health)) : "") . ($absorption > 0 ? TextFormat::GOLD . str_repeat("|", (int) $absorption) : "") . TextFormat::AQUA . "]";
		return TextFormat::GREEN . $os . TextFormat::AQUA . " [" . ($health >= 0 ? TextFormat::GREEN . str_repeat("|", (int) $health) : "") . (($max - $health) >= 0 ? TextFormat::RED . str_repeat("|", (int) ($max - $health)) : "") . ($absorption > 0 ? TextFormat::GOLD . str_repeat("|", (int) $absorption) : "") . TextFormat::AQUA . "] " . TextFormat::GREEN . $inputMode;
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $pk) : bool{
		return true;
	}

	public function setFlightMode(bool $mode = true, ?GameMode $gamemode = null, bool $doubleJumpEnabled = false): void {
		$doubleJumpEnabled = $doubleJumpEnabled || $this->getWorld()->getId() == SkyBlock::getInstance()->getSpawnPosition()->getWorld()->getId();
		if (!is_null($gamemode)) {
			parent::setFlightMode($mode, $gamemode, $doubleJumpEnabled);
			return;
		}
		$atIsland = $this->getGameSession()?->getIslands()->atIsland();
		$islandAt = $this->getGameSession()?->getIslands()->getIslandAt();
		$perms = $islandAt?->getPermissions()->getPermissionsBy($this) ?? $islandAt?->getPermissions()->getDefaultVisitorPermissions();
		if ($atIsland && ($perms->getPermission(Permissions::EDIT_BLOCKS) || $perms->getPermission(Permissions::EDIT_ORE_FROM_ORE_GENS))) {
			parent::setFlightMode($mode, GameMode::SURVIVAL(), $doubleJumpEnabled);
		} else {
			parent::setFlightMode($mode, GameMode::ADVENTURE(), $doubleJumpEnabled);
		}
	}


	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		$this->healthChanged = true;
	}

	public function setHealth(float $amount) : void{
		parent::setHealth($amount);
		if($this->isAlive()){
			$this->healthChanged = true;
		}
	}

	public function setMaxHealth(int $amount) : void{
		parent::setMaxHealth($amount);
		if($this->isAlive()){
			$this->healthChanged = true;
		}
	}

	public function isLoadedS() : bool{
		return $this->loadedSkyBlock;
	}

	public function isBleeding() : bool{
		return $this->bleedTicks > 0;
	}

	public function bleed(?Player $player, int $ticks) : void{
		$this->bleedInflict = $player;
		$this->bleedTicks += $ticks;
	}

	public function stopBleeding() : void{
		$this->bleedInflict = null;
		$this->bleedTicks = 0;
	}

	public function inGenMode() : bool{
		return $this->genMode;
	}

	public function toggleGenMode() : bool{
		return $this->genMode = !$this->genMode;
	}

	public function hasGappleCooldown() : bool{
		return $this->gappleCooldown >= time();
	}

	public function getGappleCooldown() : int{
		return $this->gappleCooldown - time();
	}

	public function setGappleCooldown() : void{
		$this->gappleCooldown = time() + 90;
	}

	public function getSpawnerKills() : int{
		return $this->spawnerKills;
	}

	public function addSpawnerKill() : void{
		$this->spawnerKills++;
	}

	public function canFly() : string|bool{
		if(!$this->isLoaded()){
			return "Not loaded";
		}
		if(Core::thisServer()->isTestServer()) return true;
		if($this->isStaff()) return true;
		if($this->getRankHierarchy() < 2){
			return "You must be at least " . TextFormat::ICON_BLAZE . " " . TextFormat::GOLD . TextFormat::BOLD . "BLAZE" . TextFormat::RESET . TextFormat::GRAY . " rank to fly! To purchase a rank, visit " . TextFormat::YELLOW . Links::SHOP;
		}
		$session = $this->getGameSession()->getKoth();
		if($session->inGame())
			return "You cannot fly in a KOTH match!";
		$session = $this->getGameSession()->getLms();
		if($session->inGame())
			return "You cannot fly in a LMS match!";
		if($this->getGameSession()->getParkour()->hasCourseAttempt())
			return "You cannot fly while in a parkour course!";
		if($this->getGameSession()->getCombat()->inPvPMode())
			return "You cannot toggle fly in PvP mode!";
		if($this->atSpawn()) return true;
		$session = $this->getGameSession()->getIslands();
		if(!$session->atIsland())
			return "You can only fly at an island!";
		$perm = ($ip = ($island = $session->getIslandAt())->getPermissions())->getPermissionsBy($this) ?? $ip->getDefaultVisitorPermissions();
		if(!$perm->getPermission(Permissions::USE_FLY))
			return "You do not have permission to fly on this island!";
		return true;
	}

	public function atSpawn() : bool{
		return $this->inLobby();
	}
	
	public function gotoPvPserver(string $reason = "") : ?ServerInstance{
		if(!($ts = Core::thisServer())->isSubServer() || $ts->getId() !== "pvp"){
			$parent = Core::getInstance()->getNetwork()->getServerManager()->getServer("skyblock-" . $ts->getTypeId() . "-pvp");
			if($parent->isOnline()){
				//$parent->transfer($this, $reason);
				SkyBlock::getInstance()->onQuit($this, true);
				if($this->isLoaded()){
					$this->getGameSession()->save(true, function($session) use ($parent, $reason) : void{
						if($this->isConnected()){
							$parent->transfer($this, $reason);
							$parent->sendSessionSavedPacket($this, 1);
						}
						$this->getGameSession()->getSessionManager()->removeSession($this);
					});
				}
				$this->sendMessage(TextFormat::YELLOW . "Saving game session data...");
				return $parent;
			}else{
				$lobby = Core::getInstance()->getNetwork()->getServerManager()->getLeastPopulated("lobby");
				if($lobby !== null && $lobby->isOnline()){
					$lobby->transfer($this, $reason);
					return $lobby;
				}else{
					//i dunno yet lol this should rarely happen
					//maybe kick?
					return null;
				}
			}
		}
		return null;
	}
	
	public function gotoSpawn(string $reason = "") : ?ServerInstance{
		SkyBlock::getInstance()->getCombat()->getArenas()->getArena()->leaveArena($this);
		if(($ts = Core::thisServer())->isSubServer()){
			/** @var SubServer $ts */
			$parent = $ts->getParentServer();
			if($parent->isOnline()){
				if(
					$this->isLoaded() &&
					($is = $this->getGameSession()->getIslands()->getIslandAt()) !== null &&
					!$is->isDeleted() &&
					($perms = $is->getPermissions()->getPermissionsBy($this)) !== null &&
					$perms->getPermission(Permissions::USE_SHOP)
				){
					($pk = new PlayerLoadActionPacket([
						"player" => $this->getName(),
						"server" => $parent->getIdentifier(),
						"action" => "lastisland",
						"actionData" => ["island" => $is->getWorldName()]
					]))->queue();
				}
				//$parent->transfer($this, $reason);
				SkyBlock::getInstance()->onQuit($this, true);
				if($this->isLoaded()){
					$this->getGameSession()->save(true, function($session) use($parent, $reason) : void{
						if($this->isConnected()){
							if(($cm = $this->getGameSession()->getCombat()->getCombatMode())->inCombat()){
								$cm->reset(false);
							}
							$parent->transfer($this, $reason);
							$parent->sendSessionSavedPacket($this, 1);
						}
						$this->getGameSession()->getSessionManager()->removeSession($this);
					});
				}
				$this->sendMessage(TextFormat::YELLOW . "Saving game session data...");
				return $parent;
			}else{
				$lobby = Core::getInstance()->getNetwork()->getServerManager()->getLeastPopulated("lobby");
				if($lobby !== null && $lobby->isOnline()){
					$lobby->transfer($this, $reason);
					return $lobby;
				}else{
					//i dunno yet lol this should rarely happen
					//maybe kick?
					return null;
				}
			}
		}else{
			$this->setGamemode(GameMode::ADVENTURE());
			$this->getGameSession()->getIslands()?->setIslandAt();
			$this->teleport(SkyBlock::getInstance()->getSpawnPosition(), 90);
			if(!empty($reason)) $this->sendMessage($reason);
			return null;
		}
	}

	public function getLastPressurePlateActivation() : int{
		return $this->lastPressurePlateActivation;
	}

	public function setLastPressurePlateActivation() : void{
		$this->lastPressurePlateActivation = time();
	}

	public function canActivatePressurePlate() : bool{
		return $this->getLastPressurePlateActivation() !== time();
	}

	public function inLobby() : bool{
		return $this->getPosition()->getWorld() === SkyBlock::getInstance()->getSpawnPosition()->getWorld();
	}
	
	public function inElytra() : bool{
		return $this->elytra;
	}

	public function getCombo() : int{
		return $this->combo;
	}

	public function addCombo() : void{
		$this->combo++;
		$this->comboTicks = 40;
	}

	public function resetCombo() : void{
		$this->combo = 0;
		$this->comboTicks = -1;
	}

	public function teleportProcess(Player $player) : void{
		if(!$this->isLoaded()) return;

		$session = $this->getGameSession();
		if($session->getIslands()->atIsland()){
			$island = $session->getIslands()->getIslandAt();
			$island?->teleportTo($player);
			return;
		}
		if(($a = SkyBlock::getInstance()->getCombat()->getArenas())->inArena($this)){
			$a->getArena()->teleportTo($player, true, false);
			return;
		}
		if(($k = SkyBlock::getInstance()->getKoth())->inGame($this)){
			$k->getGameByPlayer($this)->teleportTo($player, true, false);
			return;
		}
	}

	public function spawnTo(Player $player) : void{
		if(
			!$this->isLoaded() ||
			!($lms = $this->getGameSession()->getLms())->inGame() ||
			!$lms->getGame()->hasSpectator($this)
		){
			parent::spawnTo($player);
		}
	}

}