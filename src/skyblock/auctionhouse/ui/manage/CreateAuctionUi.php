<?php namespace skyblock\auctionhouse\ui\manage;

use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use pocketmine\{
	player\Player,
	Server
};
use pocketmine\item\Item;

use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown,
	Input
};

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\auctionhouse\{
	Auction,
	AuctionManager
};

use core\utils\TextFormat;

class CreateAuctionUi extends CustomForm{
	
	public function __construct(public Item $item, string $message = "", bool $error = true){
		parent::__construct("Create Auction");
		$this->addElement(new Label(
			($message ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
			"Use this simple menu to create an Auction! Get rid of your unneeded items!"
		));

		$this->addElement(new Input("Auction name (max 32 char)", "Auction name", "My Auction"));
		$this->addElement(new Label("Creating using: x" . $item->getCount() . " " . $item->getName()));
		$this->addElement(new Input("Starting Bid", "100", "10"));
		$this->addElement(new Input("Buy Now Price", "100", "100"));
		$this->addElement(new Label("Tapping 'Submit' will start an Auction using your item and settings. The Auction will last 24 hours or until someone directly buys the item using the Buy Now price."));
	}

	public function handle($response, Player $player) {
		/** @var SkyBlockPlayer $player */
		$name = $response[1];
		$item = $this->item;
		$startprice = (int) $response[3];
		$buynowprice = (int) $response[4];

		if($name == null){
			$player->showModal(new CreateAuctionUi($item, "Name cannot be blank!"));
			return;
		}
		if(strlen($name) > 32){
			$player->showModal(new CreateAuctionUi($item, "Auction name cannot be longer than 32 characters!"));
			return;
		}

		if($item->getCount() == 0 || $item->isNull()){
			$player->showModal(new ChooseAuctionItemUi($player, "This item cannot be put up for auction."));
			return;
		}
		if(!$player->getInventory()->contains($item)){
			$player->showModal(new ChooseAuctionItemUi($player, "The item you chose is no longer in your inventory! Please choose another one and try again."));
			return;
		}

		if($startprice < 0 || $startprice > 999999999){
			$player->showModal(new CreateAuctionUi($item, "The starting bid has to be a positive number under 999,999,999!"));
			return;
		}
		if($buynowprice < 0 || $buynowprice > 999999999){
			$player->showModal(new CreateAuctionUi($item, "Buy now price has to be a positive number under 999,999,999!"));
			return;
		}

		$auction = new Auction(AuctionManager::$auctionId++, $player->getUser(), time(), $name, $item, $startprice, $buynowprice);
		SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager()->addAuction($auction);

		$player->getInventory()->removeItem($item);
		$player->showModal(new AuctionManageUi($player, "Your Auction has been started!", false));
		$type = Core::getInstance()->getNetwork()->getServerType();
		$post = new Post("", "AuctionHouse - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $player->getName() . "** put **x" . $item->getCount() . " " . TextFormat::clean($auction->getItem()->getName()) . "** up for auction!", "", "ffb106", new Footer("Yowza | " . date("F j, Y, g:ia", time())), "", "[REDACTED]", null, [
				new Field("Bid Start Price", number_format($startprice), true),
				new Field("Buy Now Price", number_format($buynowprice), true)
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("auctions-" . $type));
		$post->send();

		foreach(Server::getInstance()->getOnlinePlayers() as $p){
			$p->sendMessage(TextFormat::PI . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " has put " . TextFormat::AQUA . "x" . $item->getCount() . " " . $item->getName() . TextFormat::RESET . TextFormat::GRAY . " in the Auction House!");
		}
	}
}