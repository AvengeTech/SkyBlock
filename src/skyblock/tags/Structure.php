<?php namespace skyblock\tags;

use core\utils\TextFormat;

class Structure{

	const TAG_FORMAT = [
		"AdminABOOSE" => TextFormat::RED . "Admin" . TextFormat::DARK_RED . "ABOOSE",
		"OwnerABOOSE" => TextFormat::BLUE . "Owner" . TextFormat::DARK_BLUE . "ABOOSE",
		"ABOOSE" => TextFormat::GOLD . "AB" . TextFormat::WHITE . "OO" . TextFormat::GOLD . "SE",

		"ShaneSucks" => TextFormat::RED . "Shane" . TextFormat::DARK_RED . "Sucks",
		"Maloner" => TextFormat::BLUE . "Malon" . TextFormat::RED . "er",
		"#GoodGuyShane" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::DARK_BLUE . "GoodGuy" . TextFormat::DARK_GREEN . "Shane",
		"#BadGuyShane" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::DARK_RED . "BadGuy" . TextFormat::GOLD . "Shane",

		"FakeDev" => TextFormat::DARK_GRAY . "Fake" . TextFormat::GOLD . "Dev",
		"RichBoi" => TextFormat::YELLOW . "Rich" . TextFormat::WHITE . "Boi",
		"#DAB" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::GOLD . "D" . TextFormat::YELLOW . "A" . TextFormat::RED . "B",
		"Mineman" => TextFormat::GRAY . "Mine" . TextFormat::GREEN . "man",
		"HoldThisL" => TextFormat::GOLD . "Hold" . TextFormat::YELLOW . "This" . TextFormat::DARK_RED . "L",
		"RoadToFree" => TextFormat::GRAY . "Road" . TextFormat::DARK_GRAY . "To" . TextFormat::YELLOW . "Free",
		"Clickbait" => TextFormat::DARK_RED . "Cli" . TextFormat::WHITE . "ckb" . TextFormat::DARK_RED . "ait",
		"N00b" => TextFormat::DARK_GRAY . "N" . TextFormat::GRAY . "00" . TextFormat::AQUA . "b",
		"Savage" => TextFormat::LIGHT_PURPLE . "Sa" . TextFormat::RED . "va" . TextFormat::LIGHT_PURPLE . "ge",
		"TryHard" => TextFormat::BOLD . TextFormat::OBFUSCATED . TextFormat::GOLD . ".." . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Try" . TextFormat::DARK_RED . "Hard" . TextFormat::OBFUSCATED . TextFormat::GOLD . ".." . TextFormat::RESET,
		"Toxic" => TextFormat::GREEN . "Tox" . TextFormat::DARK_GREEN . "ic",
		"YEET" => TextFormat::YELLOW . "YEET",
		"OOF" => TextFormat::RED . "O" . TextFormat::GOLD . "O" . TextFormat::WHITE . "F",
		"Boneless" => TextFormat::BLACK . "Bo" . TextFormat::DARK_GRAY . "ne" . TextFormat::GRAY . "le" . TextFormat::WHITE . "ss",
		"OnTheGrind" => TextFormat::GRAY . "On" . TextFormat::DARK_GRAY . "The" . TextFormat::GOLD . "Grind",
		"PeanartBarter" => TextFormat::BOLD . TextFormat::GOLD . "Peanart" . TextFormat::YELLOW . "Barter",
		"WhatTheSigma" => TextFormat::BOLD . TextFormat::BLACK . "What" . TextFormat::DARK_GRAY . "The" . TextFormat::GRAY . "Sigma",

		"#BlameJay" => TextFormat::BOLD . TextFormat::WHITE . "#" . TextFormat::GRAY . "Blame" . TextFormat::LIGHT_PURPLE . "Jay",
		"#BlameMig" => TextFormat::BOLD . TextFormat::WHITE . "#" . TextFormat::GRAY . "Blame" . TextFormat::GOLD . "Mig",
		"#BlameShane" => TextFormat::BOLD . TextFormat::WHITE . "#" . TextFormat::GRAY . "Blame" . TextFormat::YELLOW . "Shane",
		"#BlameLance" => TextFormat::BOLD . TextFormat::WHITE . "#" . TextFormat::GRAY . "Blame" . TextFormat::BLUE . "Lance",

		"Melon" => TextFormat::RED . "M" . TextFormat::GREEN . "elo" . TextFormat::RED . "n",
		"HoneyMustard" => TextFormat::GOLD . "Honey" . TextFormat::YELLOW . "Mustard",
		"#Chocaholic" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::DARK_GRAY . "Cho" . TextFormat::GOLD . "caho" . TextFormat::DARK_GRAY . "lic",

		"TechieIsBae" => TextFormat::AQUA . "Tec" . TextFormat::GOLD . "hie" . TextFormat::WHITE . "Is" . TextFormat::RED . "Bae",
		"ShaneIsBae" => TextFormat::DARK_AQUA . "Shane" . TextFormat::BLUE . "Is" . TextFormat::DARK_BLUE . "Bae",
		"ForeverAlone" => TextFormat::WHITE . "For" . TextFormat::GRAY . "ever" . TextFormat::DARK_GRAY . "Al" . TextFormat::BLACK . "one",
		"Loner" => TextFormat::GRAY . "Loner",
		"SmellyMiguelly" => TextFormat::BOLD . TextFormat::GREEN . "Smelly" . TextFormat::MINECOIN_GOLD . "Miguelly",
		"SmellyShanelly" => TextFormat::BOLD . TextFormat::MINECOIN_GOLD . "Smelly" . TextFormat::GREEN . "Shanelly",
		"BookTexturePlz" => TextFormat::BOLD . TextFormat::AQUA . "Book" . TextFormat::BLUE . "Texture" . TextFormat::DARK_BLUE . "Plz",

		"#ATFTW" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::AQUA . "A" . TextFormat::GOLD . "T" . TextFormat::GREEN . "FTW",
		"AvengeTechJuice" => TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech" . TextFormat::DARK_BLUE . "Juice",
		"ATisMyCity" => TextFormat::AQUA . "A" . TextFormat::GOLD . "T" . TextFormat::YELLOW . "is" . TextFormat::AQUA . "My" . TextFormat::GREEN . "City",
		"ATisBad" => TextFormat::AQUA . "A" . TextFormat::GOLD . "T" . TextFormat::YELLOW . "is" . TextFormat::DARK_RED . "Bad",

		"BeepBeepLettuce" => TextFormat::AQUA . "Beep" . TextFormat::GOLD . "Beep" . TextFormat::GREEN . "Lettuce",
		"AmANGERY" => TextFormat::BLACK . "Am" . TextFormat::DARK_RED . "ANG" . TextFormat::GOLD . "ERY",
		"Memz" => TextFormat::GREEN . "Mem" . TextFormat::DARK_GREEN . "z",
		"Nani" => TextFormat::BOLD . TextFormat::DARK_RED . "Nani!?",
		"MemeKing" => TextFormat::GREEN . "Meme" . TextFormat::BLUE . "King",
		"MemeQueen" => TextFormat::GREEN . "Meme" . TextFormat::RED . "Queen",
		"MemeGod" => TextFormat::GREEN . "Meme" . TextFormat::YELLOW . "God",

		"WOAH" => TextFormat::GREEN . TextFormat::OBFUSCATED . "!" . TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "WOAH" . TextFormat::GREEN . TextFormat::OBFUSCATED . "!" . TextFormat::RESET,
		"RUN" => TextFormat::DARK_RED . "R" . TextFormat::GOLD . "U" . TextFormat::DARK_RED . "N",
		"#JustDoIt" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::RED . "Just" . TextFormat::DARK_RED . "Do" . TextFormat::RED . "It",

		"OwO" => TextFormat::GOLD . "O" . TextFormat::YELLOW . "w" . TextFormat::GOLD . "O",
		"UwU" => TextFormat::GOLD . "U" . TextFormat::YELLOW . "w" . TextFormat::GOLD . "U",
		"MaloneChan" => TextFormat::BLUE . "Malone" . TextFormat::GOLD . "Chan",
		"TechieChan" => TextFormat::AQUA . "Techie" . TextFormat::GOLD . "Chan",
		"Weeb" => TextFormat::GOLD . TextFormat::OBFUSCATED . "!" . TextFormat::WHITE . "!" . TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "W" . TextFormat::AQUA . "e" . TextFormat::YELLOW . "e" . TextFormat::GREEN . "b" . TextFormat::OBFUSCATED . TextFormat::WHITE . "!" . TextFormat::GOLD . "!" . TextFormat::RESET,
		"Sidemen" => TextFormat::BOLD . TextFormat::OBFUSCATED . TextFormat::YELLOW . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "Si" . TextFormat::GRAY . "dem" . TextFormat::DARK_GRAY . "en" . TextFormat::OBFUSCATED . TextFormat::YELLOW . "ii" . TextFormat::RESET,

		"Useless" => TextFormat::GOLD . "Use" . TextFormat::DARK_RED . "less",
		"Denied" => TextFormat::DARK_RED . "Denied",
		"LOL" => TextFormat::YELLOW . "L" . TextFormat::WHITE . "O" . TextFormat::YELLOW . "L",
		"Chugger" => TextFormat::YELLOW . "Chug" . TextFormat::GOLD . "ger",
		"Baguette" => TextFormat::GOLD . "Baguette",
		"IceJuice" => TextFormat::AQUA . "Ice" . TextFormat::WHITE . "Juice",
		"FeelsBadMan" => TextFormat::GREEN . "Feels" . TextFormat::DARK_RED . "Bad" . TextFormat::DARK_GREEN . "Man",
		"#NoLife" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . TextFormat::RED . "No" . TextFormat::YELLOW . "Life",
		"KingOfAT" => TextFormat::OBFUSCATED . TextFormat::BLUE . ";" . TextFormat::AQUA . ";" . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "King" . TextFormat::WHITE . "Of" . TextFormat::AQUA . "A" . TextFormat::GOLD . "T" . TextFormat::OBFUSCATED . TextFormat::AQUA . ";" . TextFormat::BLUE . ";" . TextFormat::RESET,
		"QueenOfAT" => TextFormat::OBFUSCATED . TextFormat::DARK_PURPLE . ";" . TextFormat::LIGHT_PURPLE . ";" . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Queen" . TextFormat::WHITE . "Of" . TextFormat::AQUA . "A" . TextFormat::GOLD . "T" . TextFormat::OBFUSCATED . TextFormat::LIGHT_PURPLE . ";" . TextFormat::DARK_PURPLE . ";" . TextFormat::RESET,

		//Brand New
		"F" => "F",
		"NootNoot" => TextFormat::GOLD . "Noot" . TextFormat::WHITE . "Noot",
		"ROH" => TextFormat::DARK_RED . "ROH",
		"OraOra" => TextFormat::DARK_GREEN . "Ora" . TextFormat::GREEN . "Ora",
		"AraAra" => TextFormat::DARK_PURPLE . "A" . TextFormat::WHITE . "r" . TextFormat::LIGHT_PURPLE . "a" . TextFormat::BOLD . TextFormat::DARK_PURPLE . "A" . TextFormat::WHITE . "r" . TextFormat::LIGHT_PURPLE . "a",
		"Area51" => TextFormat::DARK_GREEN . TextFormat::OBFUSCATED . "!!" . TextFormat::RESET . TextFormat::GREEN . "Area" . TextFormat::BLACK . "51" . TextFormat::RESET . TextFormat::DARK_GREEN . TextFormat::OBFUSCATED . "!!" . TextFormat::RESET,
		"ShaneIsActive" => TextFormat::BLACK . "Shane" . TextFormat::BLUE . "Is" . TextFormat::DARK_AQUA . "Active",
		"Quirky" => TextFormat::DARK_BLUE . "Q" . TextFormat::BOLD . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "u" . TextFormat::BOLD . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "i" . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "r" . TextFormat::OBFUSCATED . TextFormat::WHITE . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::BLUE . "k" . TextFormat::OBFUSCATED . TextFormat::WHITE . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_AQUA . "y",
		"Poof" => TextFormat::AQUA . "Poof",
		"FsInTheChatBois" => TextFormat::RED . "Fs" . TextFormat::YELLOW . "In" . TextFormat::GREEN . "The" . TextFormat::AQUA . "Chat" . TextFormat::LIGHT_PURPLE . "Bois",
		"Thanos" => TextFormat::DARK_PURPLE . "Thanos",
		"ILoveYou3000" => TextFormat::LIGHT_PURPLE . "I" . TextFormat::RED . "Love" . TextFormat::LIGHT_PURPLE . "You" . TextFormat::RED . "3000",
		
		"L" => TextFormat::ITALIC . TextFormat::GOLD . "L",
		"AMOGUS" => TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "AMOGUS!" . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET,
		"SussyImposter" => TextFormat::RED . "Sussy" . TextFormat::AQUA . "Impostor",
		"Grape" => TextFormat::DARK_PURPLE . "Grape",
		"Bean" => TextFormat::YELLOW . "Bean",
		"Cheese" => TextFormat::GOLD . "Cheese",
		"Salad" => TextFormat::GREEN . "Salad",
		"PogChamp" => TextFormat::YELLOW . "Pog" . TextFormat::GRAY . "Champ",
		"WeirdChamp" => TextFormat::DARK_RED . "Weird" . TextFormat::GRAY . "Champ",
		"P2W" => TextFormat::BLUE . "P" . TextFormat::DARK_AQUA . "2" . TextFormat::AQUA . "W",
		"FixTPS" => TextFormat::DARK_GREEN . "Fix" . TextFormat::GREEN . "TPS",
		"BruhMoment" => TextFormat::DARK_PURPLE . "Bruh" . TextFormat::LIGHT_PURPLE . "Moment",
		"Spicy" => TextFormat::RED . "S" . TextFormat::DARK_RED . "p" . TextFormat::RED . "i" . TextFormat::DARK_RED . "c" . TextFormat::RED . "y",
		"Brazil" => TextFormat::DARK_GREEN . "Bra" . TextFormat::YELLOW . "zil",
		"USA" => TextFormat::RED . "U" . TextFormat::WHITE . "S" . TextFormat::BLUE . "A",
		"Japan" => TextFormat::WHITE . "Ja" . TextFormat::DARK_RED . "p" . TextFormat::WHITE . "an",
		"UK" => TextFormat::BLUE . "U" . TextFormat::DARK_RED . "K",
		"Scotland" => TextFormat::DARK_AQUA . "Scot" . TextFormat::WHITE . "land",
		"MineResetter" => TextFormat::GRAY . "Mine" . TextFormat::WHITE . "Resetter",
		"Grass" => TextFormat::DARK_GREEN . "Grass",
		"Fortnite" => TextFormat::BLUE . "Fortnite",
		"iForgor" => TextFormat::AQUA . "i" . TextFormat::DARK_GRAY . "Forgor",
		"iRember" => TextFormat::AQUA . "i" . TextFormat::YELLOW . "Rember",
		"DidIAsk" => TextFormat::DARK_PURPLE . "Did" . TextFormat::LIGHT_PURPLE . "IAsk",
		"SHEEESH" => TextFormat::OBFUSCATED . TextFormat::WHITE . "i" . TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_AQUA . "SHEEESH" . TextFormat::OBFUSCATED . TextFormat::WHITE . "i" . TextFormat::RESET,
		//"DaBaby" => TextFormat::DARK_RED . "Da" . TextFormat::RED . "Baby",
		"Crazy" => TextFormat::RED . "C" . TextFormat::BOLD . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "r" . TextFormat::BOLD . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . "a" . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "z" . TextFormat::OBFUSCATED . TextFormat::WHITE . TextFormat::OBFUSCATED . TextFormat::WHITE . "ii" . TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "y",
		"MissTheRage???" => TextFormat::DARK_AQUA . "Miss" . TextFormat::BLUE . "The" . TextFormat::AQUA . "Rage" . TextFormat::GOLD . "???",
		"FEELTHERAGE" => TextFormat::YELLOW . "FEEL" . TextFormat::GOLD . "THE" . TextFormat::RED . "RAGE",
		"Cringe" => TextFormat::BLACK . "Cringe",
		"#MadCuzBad" => TextFormat::WHITE . "#" . TextFormat::GOLD . "Mad" . TextFormat::LIGHT_PURPLE . "Cuz" . TextFormat::RED . "Bad",
		"OkBoomer" => TextFormat::GRAY . "Ok" . TextFormat::DARK_PURPLE . "Boomer",

		//"P" => TextFormat::BOLD . TextFormat::BLUE . "P",

		//NEW NEW
		"o7" => TextFormat::BOLD . TextFormat::YELLOW . "o7", //rip techno
		"crayon" => TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "C" . TextFormat::GOLD . "R" . TextFormat::YELLOW . "A" . TextFormat::GREEN . "Y" . TextFormat::BLUE . "O" . TextFormat::LIGHT_PURPLE . "N",

		//Christmas
		"Santa" => TextFormat::WHITE . "San" . TextFormat::DARK_RED . "ta",
		"Dasher" => TextFormat::DARK_GREEN . "Das" . TextFormat::GREEN . "her",
		"Dancer" => TextFormat::YELLOW . "Dan" . TextFormat::RED . "cer",
		"Prancer" => TextFormat::GRAY . "Pran" . TextFormat::WHITE . "cer",
		"Vixen" => TextFormat::DARK_GRAY . "Vi" . TextFormat::GRAY . "xen",
		"Comet" => TextFormat::DARK_PURPLE . "Co" . TextFormat::LIGHT_PURPLE . "met",
		"Cupid" => TextFormat::GOLD . "Cup" . TextFormat::GRAY . "id",
		"Donder" => TextFormat::DARK_AQUA . "Don" . TextFormat::AQUA . "der",
		"Blixen" => TextFormat::AQUA . "Bli" . TextFormat::BLUE . "xen",
		"Rudolph" => TextFormat::RED . "Rud" . TextFormat::DARK_RED . "olph",

		//Valentines
		"Lovebird" => TextFormat::DARK_RED . "Love" . TextFormat::YELLOW . "bird",
		"ILY" => TextFormat::DARK_PURPLE . "I" . TextFormat::RED . "L" . TextFormat::LIGHT_PURPLE . "Y",
		"Flirty" => TextFormat::LIGHT_PURPLE . "Fli" . TextFormat::RED . "rty",
		"Valentine" => TextFormat::DARK_PURPLE . "Val" . TextFormat::LIGHT_PURPLE . "ent" . TextFormat::RED . "ine",
		"Admirer" => TextFormat::RED . "Admir" . TextFormat::DARK_RED . "er",
		"BeMine" => TextFormat::RED . "Be" . TextFormat::LIGHT_PURPLE . "Mine",

		//Halloween
		"Spooky" => TextFormat::RESET . TextFormat::GOLD . "Sp" . TextFormat::BLACK . "oo" . TextFormat::GOLD . "ky",
		"Jack-o-Lantern" => TextFormat::RESET . TextFormat::GOLD . "Jack" . TextFormat::MINECOIN_GOLD . "-" . TextFormat::YELLOW . "o" . TextFormat::MINECOIN_GOLD . "-" . TextFormat::GOLD . "Lantern",
		"ShanesForehead" => TextFormat::RESET . TextFormat::RED . "Shane's" . TextFormat::BOLD . TextFormat::DARK_RED . "FOREHEAD",
		"Boo" => TextFormat::RESET . TextFormat::BOLD . TextFormat::BLACK . "B" . TextFormat::DARK_GRAY . "O" . TextFormat::BLACK . "O",
		"Halloween" => TextFormat::RESET . TextFormat::GOLD . "H" . TextFormat::BLACK . "a" . TextFormat::GOLD . "l" . TextFormat::BLACK . "l" . TextFormat::GOLD . "o" . TextFormat::BLACK . "w" . TextFormat::GOLD . "e" . TextFormat::BLACK . "e" . TextFormat::GOLD . "n",
		"MonsterMash" => TextFormat::RESET . TextFormat::DARK_GRAY . "Monster" . TextFormat::DARK_GREEN . "Mash",
		"SpookyScary" => TextFormat::RESET . TextFormat::BLACK . "Spooky" . TextFormat::GRAY . "Scary",
		"Witch" => TextFormat::RESET . TextFormat::DARK_PURPLE . "Witch",
		"Tombstone" => TextFormat::RESET . TextFormat::DARK_GRAY . "Tomb" . TextFormat::GRAY . "stone",
		"Ghoul" => TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "Ghoul",
		"HashSlingingSlasher" => TextFormat::RESET . TextFormat::BLACK . "Hash" . TextFormat::AQUA . "Slinging" . TextFormat::RED . "Slasher",


		//Other
		"Turkey" => TextFormat::GOLD . TextFormat::BOLD . "Turkey",
		"TurkiesOnTop" => TextFormat::GOLD . TextFormat::BOLD . "Turkies" . TextFormat::RED . "On" . TextFormat::DARK_RED . "Top",
		"HoneyBun" => TextFormat::BOLD . TextFormat::GOLD . "Honey" . TextFormat::YELLOW . "Bun",
		"PigsInSuits" => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Pigs" . TextFormat::WHITE . "In§sSuits",

		"#iVoted" => TextFormat::RESET . TextFormat::WHITE . "#" . TextFormat::BOLD . "i" . TextFormat::YELLOW . "Voted",


		//Dev
		"CaliKid" => TextFormat::RESET . TextFormat::EMOJI_SUN . TextFormat::GOLD . "Cali" . TextFormat::MINECOIN_GOLD . "Kid" . TextFormat::EMOJI_SUN,
		"Reformed>AT" => TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . 'Refo' . TextFormat::BLACK . 'rmed' . TextFormat::WHITE . TextFormat::RESET . '>' . TextFormat::BOLD . TextFormat::AQUA . 'A' . TextFormat::GOLD . 'T',
		"SquishIsHot" => TextFormat::RESET . TextFormat::LIGHT_PURPLE . "Squish" . TextFormat::RED . "Is" . TextFormat::LIGHT_PURPLE . "Hot",
		"Ploogerrag" => TextFormat::RESET . TextFormat::GRAY . "Ploogerrag",
		"RexamusRex" => TextFormat::RESET . TextFormat::BOLD . TextFormat::BLUE . 'RexamusRex'
	];

	const DISABLED_TAGS = [
		//Christmas
		"Santa",
		"Dasher",
		"Dancer",
		"Prancer",
		"Vixen",
		"Comet",
		"Cupid",
		"Dunder",
		"Blixen",
		"Rudolph",

		//Valentines
		/*
		"Lovebird",
		"ILY",
		"Flirty",
		"Valentine",
		"Admirer",
		"BeMine",
		*/

		//Halloween
		"Spooky",
		"Jack-o-Lantern",
		"ShanesForehead",
		"Boo",
		"Halloween",
		"MonsterMash",
		"SpookyScary",
		"Witch",
		"Tombstone",
		"Ghoul",
		"HashSlingingSlasher",

		//Other
		"#iVoted",

		//Dev
		"CaliKid",
		"Reformed>AT",
		"Ploogerrag",
		"SquishIsHot",
		"RexamusRex",
		"BookTexturePlz"
	];

}
