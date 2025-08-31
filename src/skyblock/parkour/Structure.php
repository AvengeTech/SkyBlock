<?php namespace skyblock\parkour;

class Structure{

	const COURSE_SKYBLOCK = 0;
	const COURSE_EASY = 0;
	const COURSE_HARD = 1;

	const COURSES = [
		self::COURSE_SKYBLOCK => [
			"name" => "SkyBlock",
			"world" => "skyblock",
			"beginning" => [-1256, 154, 3214],
			"start" => [-1260, 154, 3214],
			"checkpoints" => [
				[-1291, 162, 3217],
				[-1264, 181, 3248],
				[-1258, 187, 3278],
			],
			"end" => [-1214, 201, 3270]
		],

		self::COURSE_EASY => [
			"name" => "Easy",
			"world" => "scifi1",
			"beginning" => [-14613, 117, 13646],
			"start" => [-14613, 117, 13648],
			"checkpoints" => [
				[-14637, 114, 13692],
			],
			"end" => [-14574, 151, 13670]
		],
		self::COURSE_HARD => [
			"name" => "Hard",
			"world" => "scifi1",
			"beginning" => [-14613, 117, 13522],
			"start" => [-14613, 117, 13518],
			"checkpoints" => [
				[-14659, 89, 13485],
				[-14623, 145, 13450],
			],
			"end" => [-14562, 162, 13533]
		],
	];
	
}