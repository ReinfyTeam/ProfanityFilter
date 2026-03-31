<?php

/*
 *
 *  ____           _            __           _____
 * |  _ \    ___  (_)  _ __    / _|  _   _  |_   _|   ___    __ _   _ __ ___
 * | |_) |  / _ \ | | | '_ \  | |_  | | | |   | |    / _ \  / _` | | '_ ` _ \
 * |  _ <  |  __/ | | | | | | |  _| | |_| |   | |   |  __/ | (_| | | | | | | |
 * |_| \_\  \___| |_| |_| |_| |_|    \__, |   |_|    \___|  \__,_| |_| |_| |_|
 *                                   |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ReinfyTeam
 * @link https://github.com/ReinfyTeam/
 *
 *
 */

declare(strict_types=1);

namespace ReinfyTeam\ProfanityFilter\Utils;

final class ProfanityWordList {
	/**
	 * Default profanity words used when no custom provider is configured.
	 *
	 * @var string[]
	 */
	private const DEFAULT_PROFANITY_WORDS = [
		"anal",
		"anus",
		"arse",
		"ass",
		"ballsack",
		"balls",
		"bastard",
		"bitch",
		"biatch",
		"bloody",
		"blowjob",
		"blow job",
		"bollock",
		"bollok",
		"boner",
		"boob",
		"bugger",
		"bum",
		"butt",
		"buttplug",
		"clitoris",
		"cock",
		"coon",
		"crap",
		"cunt",
		"damn",
		"dick",
		"dildo",
		"dyke",
		"fag",
		"feck",
		"fellate",
		"fellatio",
		"felching",
		"fuck",
		"f u c k",
		"fudgepacker",
		"fudge packer",
		"flange",
		"Goddamn",
		"God damn",
		"hell",
		"homo",
		"jerk",
		"jizz",
		"knobend",
		"knob end",
		"labia",
		"muff",
		"nigger",
		"nigga",
		"penis",
		"piss",
		"prick",
		"pube",
		"pussy",
		"queer",
		"scrotum",
		"shit",
		"s hit",
		"sh1t",
		"slut",
		"smegma",
		"spunk",
		"tit",
		"tosser",
		"turd",
		"twat",
		"vagina",
		"wank",
		"whore",
		"wtf",
	];

	/**
	 * @return string[]
	 */
	public static function getDefaultList() : array {
		return self::DEFAULT_PROFANITY_WORDS;
	}
}