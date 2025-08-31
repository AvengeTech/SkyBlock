<?php

namespace skyblock\techits\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

use pocketmine\item\ItemUseResult;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\nbt\{
	NBT,
	tag\ListTag,
	tag\CompoundTag
};

use skyblock\SkyBlockPlayer;

use core\Core;
use core\discord\objects\{
	Post,
	Webhook,
	Embed,
	Field,
	Footer
};
use core\utils\TextFormat;
use pocketmine\utils\Timezone;

class TechitNote extends Item {

	public function __construct(ItemIdentifier $identifier, string $name = "Techit Note", array $enchantmentTags = []) {
		parent::__construct($identifier, $name, $enchantmentTags);
		$n = $this->getNamedTag();
		$n->setByte('isTechitNote', 1);
		$this->setNamedTag($n);
	}

	public function isInitiated(): bool {
		return (bool) $this->getNamedTag()->getByte("init", 0);
	}

	public function init(): self {
		$nbt = $this->getNamedTag();
		$nbt->setByte("init", 1);
		$this->setNamedTag($nbt);

		$this->setCustomName(TextFormat::RESET . TextFormat::AQUA . "Techit Note");
		$lores = [];
		$lores[] = TextFormat::GRAY . "This Techit Note is worth";
		$lores[] = TextFormat::AQUA . number_format($this->getTechits()) . " Techits! " . TextFormat::GRAY . "Tap the ground";
		$lores[] = TextFormat::GRAY . "to claim your Techits!";
		foreach ($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);
		$this->getNamedTag()->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
		return $this;
	}

	protected function serializeCompoundTag(CompoundTag $tag): void {
		parent::serializeCompoundTag($tag);
		if ($tag->getByte("init", 0) == 1)
			$tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
	}

	public function setup(null|string|Player $createdBy = null, int $techits = 1): self {
		$nbt = $this->getNamedTag();
		$nbt->setString("creator", $createdBy instanceof Player ? $createdBy->getName() : ($createdBy ?? "Unknown"));
		$nbt->setInt("techits", $techits);
		$nbt->setString('techitID', self::generateTechitID());
		$this->setNamedTag($nbt);

		$this->init();

		return $this;
	}

	public static function generateTechitID(): string {
		$fin = '';
		$charHash = [
			["a", "f", "r", 'n', 'l', 'o', 'p', 'y', 's', 'q'],
			["z", 'v', 'b', 'w', 'd', 'f', 'h', 'j', 'k', 'm'],
			['u', 'i', 'o', 'd', 'f', 'g', 'h', 'v', 'c', 'x']
		];
		foreach (str_split(bin2hex(random_bytes(2)) . date('yzH')) as $_ => $char) {
			if (intval($char) > 0 || (string)$char == "0" && !($_ % 2 == 0 && $_ > 0)) {
				$fin .= $charHash[mt_rand(0, 2)][intval($char)];
			} else {
				$fin .= $char;
			}
		}
		return $fin;
	}

	public function getTechits(): int {
		return $this->getNamedTag()->getInt("techits", 0);
	}

	public function getCreatedBy(): string {
		return $this->getNamedTag()->getString("creator", "unknown");
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult {
		if ($this->getNamedTag()->getByte('isTechitNote', 0) != 1) return ItemUseResult::FAIL();

		/** @var SkyBlockPlayer $player */
		$before = $player->getTechits();
		$player->addTechits($this->getTechits());
		$after = $player->getTechits();
		$player->sendMessage(TextFormat::GN . "Claimed " . TextFormat::AQUA . number_format($this->getTechits()) . " Techits!");
		$this->pop();
		$player->getInventory()->setItemInHand($this);

		$post = new Post("", "Pay Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $player->getName() . "** just claimed a Techit Note worth **" . number_format($this->getTechits()) . " techits**", "", "ffb106", new Footer("Sheeeeeeeesh | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Before", number_format($before), true),
				new Field("After", number_format($after), true),
				new Field("Created by", $this->getCreatedBy(), true),
				new Field("Note ID", $this->getNamedTag()->getString('techitID', "NONE (Old note)"), true)
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("skyblock-paylog"));
		$post->send();

		return ItemUseResult::SUCCESS();
	}
}
