<?php 

namespace skyblock\koth;

class Structure{

	const DATA_NAME = "name";
	const DATA_WORLD = "world";
	const DATA_TIME = "time";
	const DATA_CORNERS = "corners";
	const DATA_SPAWNS = "spawnpoints";
	const DATA_GLASS = "glass";
	const DATA_CENTER = "center";
	const DATA_DISTANCE = "distance-to-collect";

	const MAP_FOREST = "forest";
	const MAP_TREE = "tree";
	const MAP_BIRCH = "birch";
	const MAP_FANTASY = "fantasy";
	const MAP_MESA = "mesa";
	const MAP_MUSHROOMS = "mushrooms";
	const MAP_ORIENTAL = "oriental";
	const MAP_DESERT = "desert";
	const MAP_PUMPKIN = "pumpkin";
	const MAP_BIRDY = "birdy";
	const MAP_TUNDRA = "tundra";
	const MAP_TAIGA = "taiga";
	const MAP_METEORS = "meteors";

	const GAMES = [
		self::MAP_FOREST => [
			self::DATA_NAME => "Forest",
			self::DATA_WORLD => "koth-magicforest",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[-57, -53],
				[57, 53],
			],

			self::DATA_SPAWNS => [
				[20.5, 59, -19.5],
				[33.5, 61, -1.5],
				[13.5, 59, -34.5],
			],
			self::DATA_GLASS => [
				[-1, 63, 6],
				[0, 63, 6],
				[0, 63, 5],
				[0, 63, 4],
				[1, 63, 6],

				[-1, 63, -6],
				[0, 63,-6],
				[0, 63, -5],
				[0, 63, -4],
				[1, 63, -6],

				[-6, 63, 1],
				[-6, 63, 0],
				[-5, 63, 0],
				[-4, 63, 0],
				[-6, 63, -1],

				[6, 63, 1],
				[6, 63, 0],
				[5, 63, 0],
				[4, 63, 0],
				[6, 63, -1],

				[5, 63, 3],
				[5, 63, 4],
				[4, 63, 3],
				[4, 63, 4],
				[4, 63, 5],
				[3, 63, 4],
				[3, 63, 5],

				[5, 63, -3],
				[5, 63, -4],
				[4, 63, -3],
				[4, 63, -4],
				[4, 63, -5],
				[3, 63, -4],
				[3, 63, -5],

				[-5, 63, 3],
				[-5, 63, 4],
				[-4, 63, 3],
				[-4, 63, 4],
				[-4, 63, 5],
				[-3, 63, 4],
				[-3, 63, 5],

				[-5, 63, -3],
				[-5, 63, -4],
				[-4, 63, -3],
				[-4, 63, -4],
				[-4, 63, -5],
				[-3, 63, -4],
				[-3, 63, -5],
			],
			self::DATA_CENTER => [0, 64, 0],
			self::DATA_DISTANCE => 7,
		],
		self::MAP_TREE => [
			self::DATA_NAME => "Tree",
			self::DATA_WORLD => "treekoth1",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[215, 216],
				[315, 316],
			],

			self::DATA_SPAWNS => [
				[263.5, 75, 304.5],
				[247.5, 76, 228.5],
				[272.5, 76, 294.5],
				[224.5, 75, 259.5],
				[300.5, 75, 258.5],
			],
			self::DATA_GLASS => [
				[268, 81, 265],
				[268, 81, 266],
				[268, 81, 267],
				[267, 81, 264],
				[267, 81, 265],
				[267, 81, 266],
				[267, 81, 267],
				[267, 81, 268],
				[266, 81, 263],
				[266, 81, 264],
				[266, 81, 265],
				[266, 81, 266],
				[266, 81, 267],
				[266, 81, 268],
				[266, 81, 269],
				[265, 81, 263],
				[265, 81, 264],
				[265, 81, 265],
				[265, 81, 267],
				[265, 81, 268],
				[265, 81, 269],
				[264, 81, 263],
				[264, 81, 264],
				[264, 81, 265],
				[264, 81, 266],
				[264, 81, 267],
				[264, 81, 268],
				[264, 81, 269],
				[263, 81, 264],
				[263, 81, 265],
				[263, 81, 266],
				[263, 81, 267],
				[263, 81, 268],
				[263, 81, 269],
				[262, 81, 265],
				[262, 81, 266],
				[262, 81, 267],
			],
			self::DATA_CENTER => [265, 82, 266],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_BIRCH => [
			self::DATA_NAME => "Birch",
			self::DATA_WORLD => "koths",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[256, 256],
				[307, 307],
			],

			self::DATA_SPAWNS => [
				[303.5, 69, 277.5],
				[270.5, 70, 305.5],
				[258.5, 69, 265.5],
				[289.5, 69, 303.5],
				[266.5, 69, 272.5],
			],

			self::DATA_GLASS => [
				[281, 66, 279],
				[281, 66, 280],
				[281, 66, 282],
				[281, 66, 283],
				[282, 66, 280],
				[282, 66, 281],
				[282, 66, 282],
				[283, 66, 281],
				[280, 66, 280],
				[280, 66, 281],
				[280, 66, 282],
				[279, 66, 281],
			],
			self::DATA_CENTER => [281, 67, 281],
			self::DATA_DISTANCE => 6,
		],
		self::MAP_FANTASY => [
			self::DATA_NAME => "Fantasy",
			self::DATA_WORLD => "koths",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[268, 456],
				[369, 557],
			],

			self::DATA_SPAWNS => [
				[281.5, 83, 471.5],
				[320.5, 73, 465.5],
				[353.5, 82, 473.5],
				[365.5, 74, 529.5],
				[358.5, 74, 546.5],
				[314.5, 74, 487.5],
			],

			self::DATA_GLASS => [
				[328, 81, 515],
				[329, 81, 515],
				[330, 81, 515],
				[331, 81, 515],
				[331, 81, 516],
				[331, 81, 517],
				[331, 81, 518],

				[327, 81, 515],
				[327, 81, 516],
				[327, 81, 517],
				[327, 81, 518],

				[327, 81, 519],
				[328, 81, 519],
				[329, 81, 519],
				[330, 81, 519],
				[331, 81, 519],
			],
			self::DATA_CENTER => [329, 82, 517],
			self::DATA_DISTANCE => 4,
		],
		self::MAP_MESA => [
			self::DATA_NAME => "Mesa",
			self::DATA_WORLD => "koths",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[458, 270],
				[507, 320],
			],

			self::DATA_SPAWNS => [
				[505.5, 79, 275.5],
				[483.5, 78, 277.5],
				[460.5, 78, 288.5],
				[487.5, 78, 301.5],
				[504.5, 78, 310.5],
				[478.5, 78, 317.5],
			],

			self::DATA_GLASS => [
				[484, 77, 290],
				[485, 77, 289],
				[484, 77, 288],
				[483, 77, 289],
			],
			self::DATA_CENTER => [484, 78, 289],
			self::DATA_DISTANCE => 4,
		],
		self::MAP_MUSHROOMS => [
			self::DATA_NAME => "Mushrooms",
			self::DATA_WORLD => "koths",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[296, 57],
				[347, 108],
			],

			self::DATA_SPAWNS => [
				[301.5, 79, 62.5],
				[305.5, 79, 104.5],
				[321.5, 78, 102.5],
				[345.5, 79, 98.5],
				[342.5, 78, 75.5],
				[323.5, 78, 59.5],
			],

			self::DATA_GLASS => [
				[325, 78, 85],
				[325, 78, 83],
				[323, 78, 85],
				[323, 78, 83],
			],
			self::DATA_CENTER => [324, 79, 84],
			self::DATA_DISTANCE => 4,
		],
		self::MAP_ORIENTAL => [
			self::DATA_NAME => "Oriental",
			self::DATA_WORLD => "koths",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[2, 212],
				[103, 313],
			],

			self::DATA_SPAWNS => [
				[11.5, 74, 233.5],
				[8.5, 73, 262.5],
				[10.5, 74, 289.5],
				[53.5, 74, 303.5],
				[71.5, 73, 289.5],
				[98.5, 74, 281.5],
				[93.5, 75, 264.5],
				[100.5, 74, 234.5],
				[76.5, 73, 236.5],
			],

			self::DATA_GLASS => [
				[51, 72, 262],
				[51, 72, 264],
				[53, 72, 262],
				[53, 72, 264],
			],
			self::DATA_CENTER => [52, 73, 263],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_DESERT => [
			self::DATA_NAME => "Desert Village",
			self::DATA_WORLD => "koths-new",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[256, 254],
				[357, 355],
			],

			self::DATA_SPAWNS => [
				[337.5, 86, 325.5],
				[330.5, 85, 341.5],
				[306.5, 71, 350.5],
				[267.5, 72, 341.5],
				[280.5, 80, 333.5],
				[284.5, 71, 267.5],
				[337.5, 73, 282.5],
				[266.5, 71, 297.5],
			],

			self::DATA_GLASS => [
				[305, 70, 302],
				[309, 70, 302],
				[309, 70, 306],
				[305, 70, 306],
			],
			self::DATA_CENTER => [307, 71, 304],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_PUMPKIN => [
			self::DATA_NAME => "Pumpkin Wasteland",
			self::DATA_WORLD => "koths-new",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[1, 250],
				[102, 351],
			],

			self::DATA_SPAWNS => [
				[51.5, 72, 272.5],
				[10.5, 71, 273.5],
				[12.5, 74, 320.5],
				[70.5, 74, 336.5],
				[90.5, 76, 334.5],
				[99.5, 70, 307.5],
				[78.5, 81, 280.5],
				[34.5, 70, 255.5],
			],

			self::DATA_GLASS => [
				[49, 76, 303],
				[49, 76, 299],
				[53, 76, 299],
				[53, 76, 303],
			],
			self::DATA_CENTER => [51, 77, 301],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_BIRDY => [
			self::DATA_NAME => "Birdy",
			self::DATA_WORLD => "koths-new",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[314, 36],
				[415, 137],
			],

			self::DATA_SPAWNS => [
				[336.5, 97, 114.5],
				[321.5, 95, 78.5],
				[338.5, 102, 57.5],
				[378.5, 94, 48.5],
				[395.5, 104, 68.5],
				[388.5, 101, 124.5],
				[349.5, 94, 131.5],
				[334.5, 97, 114.5],
			],

			self::DATA_GLASS => [
				[365, 93, 89],
				[361, 93, 89],
				[361, 93, 85],
				[365, 93, 85],
			],
			self::DATA_CENTER => [363, 94, 87],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_TUNDRA => [
			self::DATA_NAME => "Tundra",
			self::DATA_WORLD => "koths-new",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[292, 588],
				[393, 689],
			],

			self::DATA_SPAWNS => [
				[389.5, 76, 648.5],
				[369.5, 76, 592.5],
				[320.5, 79, 608.5],
				[299.5, 76, 650.5],
				[314.5, 78, 666.5],
				[350.5, 76, 677.5],
				[375.5, 79, 664.5],
				[378.5, 75, 616.5],
			],

			self::DATA_GLASS => [
				[344, 75, 640],
				[344, 75, 636],
				[340, 75, 636],
				[340, 75, 640],
			],
			self::DATA_CENTER => [342, 76, 638],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_TAIGA => [
			self::DATA_NAME => "Taiga",
			self::DATA_WORLD => "koths-new",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[512, 284],
				[613, 385],
			],

			self::DATA_SPAWNS => [
				[591.5, 81, 359.5],
				[564.5, 75, 378.5],
				[520.5, 75, 365.5],
				[516.5, 75, 330.5],
				[522.5, 75, 288.5],
				[555.5, 75, 293.5],
				[582.5, 76, 313.5],
				[610.5, 75, 336.5],
			],

			self::DATA_GLASS => [
				[564, 74, 332],
				[560, 74, 332],
				[560, 74, 336],
				[564, 74, 336],
			],
			self::DATA_CENTER => [562, 75, 334],
			self::DATA_DISTANCE => 4,
		],

		self::MAP_METEORS => [
			self::DATA_NAME => "Meteors",
			self::DATA_WORLD => "koths-new",
			self::DATA_TIME => 0,

			self::DATA_CORNERS => [
				[790, 288],
				[891, 389],
			],

			self::DATA_SPAWNS => [
				[795.5, 82, 339.5],
				[801.5, 85, 312.5],
				[831.5, 82, 293.5],
				[858.5, 84, 312.5],
				[888.5, 82, 335.5],
				[889.5, 82, 370.5],
				[836.5, 82, 383.5],
				[807.5, 85, 377.5],
			],

			self::DATA_GLASS => [
				[839, 81, 336],
				[843, 81, 336],
				[843, 81, 340],
				[839, 81, 340],
			],
			self::DATA_CENTER => [841, 82, 338],
			self::DATA_DISTANCE => 4,
		],

	];

}