<?php namespace skyblock\islands;

use pocketmine\block\{
	BlockFactory,
	BlockIdentifier,
	BlockLegacyIds,
	BlockBreakInfo,
	BlockToolType
};
use pocketmine\item\ToolTier;
use pocketmine\entity\{
	EntityDataHelper,
	EntityFactory,
};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

use skyblock\SkyBlock;
use skyblock\islands\challenge\Challenges;
use skyblock\islands\command\IslandCommand;
use skyblock\islands\entity\IslandEntity;
use skyblock\islands\invite\{
	InviteManager,
};
use skyblock\islands\warp\block\StonePressurePlate;

class Islands{

	public Challenges $challenges;

	public IslandManager $islandManager;
	public InviteManager $inviteManager;

	public function __construct(public SkyBlock $plugin){
		EntityFactory::getInstance()->register(IslandEntity::class, function(World $world, CompoundTag $nbt) : IslandEntity{
			return new IslandEntity(EntityDataHelper::parseLocation($nbt, $world), null);
		}, ["skyblock:island"]);

		$this->islandManager = new IslandManager();
		$this->inviteManager = new InviteManager();

		$this->challenges = new Challenges($this, $plugin);

		$plugin->getServer()->getCommandMap()->register("island", new IslandCommand($plugin, "island", "Opens up the island menu"));
	}

	public function getChallenges() : Challenges{
		return $this->challenges;
	}

	public function getIslandManager() : IslandManager{
		return $this->islandManager;
	}

	public function getInviteManager() : InviteManager{
		return $this->inviteManager;
	}

	public function close() : void{
		$this->getIslandManager()->close();
	}

	public function tick() : void{
		$this->getIslandManager()->tick();
		$this->getInviteManager()->tick();
	}

}