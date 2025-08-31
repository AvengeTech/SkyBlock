<?php 

namespace skyblock\koth\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use skyblock\koth\pieces\claim\FullClaim;
use skyblock\koth\pieces\claim\LimitedClaim;
use skyblock\koth\pieces\Game;
use skyblock\SkyBlock;

class CenterCrystal extends Entity{

	private string $lastName = "";

	public function __construct(
		Location $location,
		private ?Game $game = null
	){
		parent::__construct($location);

		if(is_null($game)){
			$this->flagForDespawn();
			return;
		}

		$this->setNametagVisible(true);
		$this->getNetworkProperties()->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
	}

	public static function getNetworkTypeId() : string{ return EntityIds::ENDER_CRYSTAL; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.0, 1.0); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function getName() : string{ return "Center Crystal"; }

	public function getGame() : Game{ return $this->game; }

	public function canSaveWithChunk() : bool{ return false; }

	public function attack(EntityDamageEvent $source) : void{ $source->cancel(); }

	public function entityBaseTick(int $tickDiff = 1) : bool{
		parent::entityBaseTick($tickDiff);

		$game = $this->getGame();

		if($game !== null && $game->isActive()){
			/** @var LimitedClaim|FullClaim|null $claim */
			$claim = $game->getClaim();
			$claimer = $claim?->getClaimer();

			if($claimer === null){
				$this->setNametag(TF::YELLOW . "No one claiming.");
			}else{
				if($claimer instanceof Player){
					$name = match($game->getType()){
						Game::TYPE_FULL => TF::RED . $claimer->getName() . ": " . TF::YELLOW . gmdate("i:s", (time() - ($claim->getTime() - 300))) . TF::GRAY . "/" . TF::GREEN . "05:00",
						Game::TYPE_LIMITED => TF::RED . $claimer->getName() . ": " . TF::YELLOW . gmdate("i:s", $claim->getTimes()[$claimer->getXuid()] ?? 0),
						Game::TYPE_UNKNOWN => TF::RED . "SHOULD NOT APPEAR"
					};

					$this->setNameTag($name);
					
					if($claimer->getName() !== $this->lastName){
						$this->lastName = $claimer->getName();
						SkyBlock::getInstance()->getCombat()->strikeLightning($this->getPosition(), $this);
					}
				}else{
					$this->setNametag(TF::YELLOW . "No one claiming.");
				}
			}
		}

		return $this->isAlive();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties): void{
		parent::syncNetworkData($properties);
		$properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
	}
}