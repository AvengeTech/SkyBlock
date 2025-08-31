<?php

namespace skyblock;

use pocketmine\player\Player;

use skyblock\{
	crates\CratesComponent,
	combat\CombatComponent,
	data\DataComponent,
	enchantments\EnchantmentsComponent,
	enchantments\EssenceComponent,
	fishing\FishingComponent,
	games\GameComponent,
	islands\IslandsComponent,
	kits\KitsComponent,
	koth\KothComponent,
	lms\LmsComponent,
	parkour\ParkourComponent,
	pets\PetsComponent,
	spawners\SpawnersComponent,
	tags\TagsComponent,
	techits\TechitsComponent,
	trade\TradeComponent,
	quests\QuestsComponent
};
use skyblock\settings\SkyBlockSettings;

use core\session\{
	PlayerSession,
	SessionManager
};
use core\settings\SettingsComponent;
use core\user\User;
use core\utils\{
	PlaytimeComponent,
	Version
};

class SkyBlockSession extends PlayerSession {

	public function __construct(SessionManager $sessionManager, Player|User $user) {
		parent::__construct($sessionManager, $user);

		$this->addComponent(new CratesComponent($this));
		$this->addComponent(new CombatComponent($this));
		$this->addComponent(new DataComponent($this));
		$this->addComponent(new EnchantmentsComponent($this));
		$this->addComponent(new EssenceComponent($this));
		$this->addComponent(new FishingComponent($this));
		$this->addComponent(new GameComponent($this));
		$this->addComponent(new IslandsComponent($this));
		$this->addComponent(new KitsComponent($this));
		$this->addComponent(new KothComponent($this));
		$this->addComponent(new LmsComponent($this));
		$this->addComponent(new ParkourComponent($this));
		$this->addComponent(new PetsComponent($this));
		$this->addComponent(new SpawnersComponent($this));
		$this->addComponent(new TagsComponent($this));
		$this->addComponent(new TechitsComponent($this));
		$this->addComponent(new TradeComponent($this));
		$this->addComponent(new QuestsComponent($this));

		$this->addComponent(new PlaytimeComponent($this));
		$this->addComponent(new SettingsComponent(
			$this,
			Version::fromString(SkyBlockSettings::VERSION),
			SkyBlockSettings::DEFAULT_SETTINGS,
			SkyBlockSettings::SETTING_UPDATES
		));
	}

	public function updateInventory(array $inventory, array $armor, array $cursor) {
		if ($this->getData()->isLoaded()) {
			$this->getData()->inventory = $inventory;
			$this->getData()->armorinventory = $armor;
			$this->getData()->saveAsync(false);
		} else parent::updateInventory($inventory, $armor, $cursor);
	}

	public function updateEnderInventory(array $ender) {
		if ($this->getData()->isLoaded()) {
			$this->getData()->enderchest_inventory = $ender;
			$this->getData()->saveAsync(false);
		} else parent::updateEnderInventory($ender);
	}

	public function getCrates(): CratesComponent {
		return $this->getComponent("crates");
	}

	public function getCombat(): CombatComponent {
		return $this->getComponent("combat");
	}

	public function getData(): DataComponent {
		return $this->getComponent("data");
	}

	public function getEnchantments(): EnchantmentsComponent {
		return $this->getComponent("enchantments");
	}

	public function getEssence(): EssenceComponent {
		return $this->getComponent("essence");
	}

	public function getFishing(): FishingComponent {
		return $this->getComponent("fishing");
	}

	public function getGames(): GameComponent {
		return $this->getComponent("game");
	}

	public function getIslands(): IslandsComponent {
		return $this->getComponent("islands");
	}

	public function getKits(): KitsComponent {
		return $this->getComponent("kits");
	}

	public function getKoth(): KothComponent {
		return $this->getComponent("koth");
	}

	public function getLms(): LmsComponent {
		return $this->getComponent("lms");
	}

	public function getParkour(): ParkourComponent {
		return $this->getComponent("parkour");
	}

	public function getPets() : PetsComponent{
		return $this->getComponent("pets");
	}

	public function getSpawners(): SpawnersComponent {
		return $this->getComponent("spawners");
	}

	public function getTags(): TagsComponent {
		return $this->getComponent("tags");
	}

	public function getTechits(): TechitsComponent {
		return $this->getComponent("techits");
	}

	public function getTrade(): TradeComponent {
		return $this->getComponent("trade");
	}

	public function getQuests(): QuestsComponent {
		return $this->getComponent("quests");
	}

	public function getPlaytime(): PlaytimeComponent {
		return $this->getComponent("playtime");
	}

	public function getSettings(): SettingsComponent {
		return $this->getComponent("settings");
	}
}
