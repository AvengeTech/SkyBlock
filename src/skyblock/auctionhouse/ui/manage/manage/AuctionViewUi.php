<?php namespace skyblock\auctionhouse\ui\manage\manage;

use pocketmine\player\Player;
use pocketmine\item\Durable;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\auctionhouse\ui\manage\AuctionManageUi;
use skyblock\auctionhouse\Auction;
use skyblock\techits\item\TechitNote;
use skyblock\crates\item\KeyNote;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class AuctionViewUi extends SimpleForm{
	
	public function __construct(public Auction $auction){
		$item = $auction->getItem();

		$ench = $item->getEnchantments();
		$el = "";
		foreach($ench as $e){
			$ee = ($ens = SkyBlock::getInstance()->getEnchantments())->getEWE($e);
			if($ee !== null) $el .= "- " . $ee->getName() . " " . $ens->getRoman($e->getLevel()) . PHP_EOL;
		}
		parent::__construct($auction->getName(),
			"Auction name: " . $auction->getName() . PHP_EOL .
			"Item: " . TextFormat::AQUA . "x" . $item->getCount() . " " . $item->getName() . ($item->hasCustomName() ? " (" . $item->getVanillaName() . ")" : "") . TextFormat::RESET . TextFormat::WHITE . PHP_EOL .
			($item instanceof Durable ?
				"Used: " . ($item->getDamage() > 0 ? TextFormat::GREEN . "YES" : TextFormat::RED . "NO") . TextFormat::WHITE . PHP_EOL .
				($item->hasEnchantments() ?
					"Enchantments:" . PHP_EOL . $el . PHP_EOL
				: "")
			: (
				$item instanceof TechitNote ? "Techit value: " . $item->getTechits() . PHP_EOL : (
					$item instanceof KeyNote ?
						"Key value: x" . $item->getWorth() . " " . $item->getType() . PHP_EOL :
						""
					)
				)
			) .
			($auction->getBidder() !== null ?
				"Highest bid: " . TextFormat::AQUA . number_format($auction->getBid()) . " techits" . TextFormat::WHITE . PHP_EOL .
				"Bidder: " . TextFormat::YELLOW . $auction->getBidder()->getGamertag()
			:
				"Starting bid: " . TextFormat::AQUA . number_format($auction->getStartingBid()) . " techits" . TextFormat::WHITE . PHP_EOL .
				TextFormat::RED . "No bids have been placed yet!"
			) . TextFormat::WHITE . PHP_EOL . PHP_EOL .

			"Buy now price: " . TextFormat::AQUA . number_format($auction->getBuyNowPrice()) . " techits"
		);

		$this->addButton(new Button("Remove auction"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		if($response == 0){
			$player->showModal(new AuctionDeleteUi($this->auction));
			return;
		}
		if($response == 1){
			$player->showModal(new AuctionManageUi($player));
			return;
		}
	}

}