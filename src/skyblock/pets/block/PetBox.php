<?php

namespace skyblock\pets\block;

use core\utils\ItemRegistry;
use core\utils\TextFormat as TF;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\islands\permission\Permissions;
use skyblock\pets\event\UnlockPetBoxEvent;
use skyblock\pets\item\PetKey;
use skyblock\pets\Structure;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class PetBox extends Opaque{

	public function getPickedItem(bool $addUserData = false): Item{
		return $this->addData($this->asItem());
	}

	public function getDrops(Item $item) : array{
		return [$this->addData($this->asItem())];
	}

	public function addData(Item $item) : Item{
		$item->setCustomName(TF::RESET . TF::WHITE . "Pet Box");

		$lores = [];
		$lores[] = "Box that contains a pet";
		$lores[] = "inside. A pet key will";
		$lores[] = "be needed to unlock";
		$lores[] = "the box.";

		foreach($lores as $key => $lore){
			$lores[$key] = TF::RESET . TF::GRAY . $lore;
		}
		$item->setLore($lores);
		return $item;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!$player instanceof SkyBlockPlayer) return false;

		if($item instanceof PetKey){
			$isession = $player->getGameSession()->getIslands();
			$island = $isession->getIslandAt();

			if(is_null($island)) return false;

			$perms = $island->getPermissions()->getPermissionsBy($player);
			
			if(is_null($perms)) return false;
 			if(!$perms->getPermission(Permissions::EDIT_BLOCKS)) return false;

			$item->pop();
			$player->getInventory()->setItemInHand($item);

			$chance = round(lcg_value() * 100);

			if($chance <= 65){
				$rarity = Structure::RARITY_RARE;
			}elseif($chance <= 90){
				$rarity = Structure::RARITY_LEGENDARY;
			}else{
				$rarity = Structure::RARITY_DIVINE;
			}

			$pets = SkyBlock::getInstance()->getPets()->getPetsByRarity($rarity);
			$id = $pets[array_rand($pets)];

			$egg = ItemRegistry::PET_EGG()->setup($id)->init();

			if(!$player->getInventory()->canAddItem($egg)){
				$player->sendMessage(TF::RI . "You need to free an inventory space!");
				return false;
			}

			$pos = $this->getPosition();
			$sound = PlaySoundPacket::create(
				"firework.large_blast",
				$pos->x,
				$pos->y,
				$pos->z,
				0.50,
				1.0
			);

			$particles = [
				new AngryVillagerParticle,
				new EndermanTeleportParticle,
				new HappyVillagerParticle,
				new FlameParticle
			];

			for($i = 0; $i < mt_rand(30, 35); $i++){
				$particle = $particles[array_rand($particles)];
				$pos->getWorld()->addParticle($pos->add(mt_rand(-10, 10) / 10, mt_rand(-10, 10) / 10, mt_rand(-10, 10) / 10), $particle);
				$pos->getWorld()->broadcastPacketToViewers($this->getPosition(), $sound);
			}

			$ev = new UnlockPetBoxEvent($player, $this, $egg);
			$ev->call();

			$pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());
			$player->getInventory()->addItem($egg);

			$player->sendTitle(
				ED::rarityColor($rarity) . ED::rarityName($rarity) . "",
				TF::GRAY . "You got a " . ED::rarityColor($rarity) . Structure::PETS[$id][Structure::DATA_NAME] . TF::GRAY . " pet!"
			);
			return true;
		}

		$player->sendMessage(TF::RI . "You need a " . TF::MINECOIN_GOLD . "Pet Key" . TF::GRAY . " to open this box!");
		return false;
	}
}