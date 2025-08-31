<?php namespace skyblock\fishing\item;

use core\utils\TextFormat as TF;
use core\utils\TextFormat;
use pocketmine\block\Lava;
use pocketmine\item\{
	Durable,
	ItemUseResult
};
use pocketmine\entity\{
	Entity,
	Living,
	Location
};
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;

use skyblock\enchantments\ItemData;
use skyblock\fishing\entity\Hook;
use skyblock\settings\SkyBlockSettings;
use skyblock\SkyBlockPlayer;

class FishingRod extends Durable{

	private ?Hook $hook = null;

	public function getMaxStackSize() : int{ return 1; }

	public function getMaxDurability() : int{ return 355; }

	public function getHook() : ?Hook{ return $this->hook; }

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		if(
			!$player instanceof SkyBlockPlayer || 
			($player->atSpawn() || $player->getGameSession()->getCombat()->isInvincible())
		) return ItemUseResult::FAIL();

		$session = $player->getGameSession()->getFishing();
		if($session->isFishing()){
			if($session->getHook()->getLiquidType() !== Hook::LIQUID_NONE){
				$damage = 1;
			}else{
				$damage = mt_rand(2, 3);
			}
			$this->applyDamage($damage);

			if(
				(($this->getMaxDurability() - $this->getDamage()) / $this->getMaxDurability()) * 100 <= 45 &&
				$player->getGameSession()->getSettings()->getSetting(SkyBlockSettings::TOOL_BREAK_ALERT)
			){
				$player->sendTip(TextFormat::EMOJI_CAUTION . TextFormat::YELLOW . " Tool has " . TextFormat::RED . ($this->getMaxDurability() - $this->getDamage()) . TextFormat::YELLOW . " durability left!");
			}

			if($session->getHook()->reel($this)){
				$data = new ItemData($this);
				$leveledUp = $data->addCatch();
				$data->apply($this);
				if($leveledUp){
					$data->sendLevelUpTitle($player);
				}
			}
			$session->setFishing();
		}else{
			if($session->isHooked()){
				$this->drag($player, $session->getHooked());
				$session->setHooked();

				$this->applyDamage(mt_rand(1, 2));
			}else{
				// prevent hook bugging out in lava
				if($player->getWorld()->getBlock($player->getPosition()) instanceof Lava){
					$player->sendMessage(TF::RI . "You can not fish while in lava");

					return ItemUseResult::FAIL();
				}

				$this->hook = $hook = new Hook(Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->yaw, $player->getLocation()->pitch), $player, $this);
				$hook->setMotion($player->getDirectionVector()->multiply(0.8));
				$hook->spawnToAll();

				$player->getWorld()->addSound($player->getPosition(), new ThrowSound());
				$session->setFishing($hook);
			}
		}
		return ItemUseResult::SUCCESS();
	}

	public function drag(Entity $to, Entity $from, float $pull = 0.8) : void{
		if(!$from instanceof Living) return;

		$dv = $to->getPosition()->subtract($from->getPosition()->x, $from->getPosition()->y, $from->getPosition()->z)->normalize();
		$from->knockBack($dv->x, $dv->z, $pull);
	}

}