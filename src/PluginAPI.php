<?php

/*
 *
 * __  __   __ _  __      __ | |_  __  __   ___    _ __
 * \ \/ /  / _` | \ \ /\ / / | __| \ \/ /  / _ \  | '_ \
 *  >  <  | (_| |  \ V  V /  | |_   >  <  | (_) | | | | |
 * /_/\_\  \__, |   \_/\_/    \__| /_/\_\  \___/  |_| |_|
 *            |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
 *
 */

declare(strict_types=1);

namespace xqwtxon\ProfanityFilter;

use function mb_strlen;
use function preg_match;
use function preg_replace;
use function sizeof;
use function str_repeat;

final class PluginAPI {

	/**
	 * Whether to detect message on provided words.
	 */
	public static function detectProfanity(string $message, array $words) : bool {
		$filterCount = sizeof($words);
		for ($i = 0; $i < $filterCount; $i++) {
			$condition = preg_match("/" . $words[$i] . "/iu", $message) > 0;
			if ($condition) {
				return true;
			}
		}
		return false;
	}

	/**
	 * It is being used to remove profanities on message.
	 * Returns string convert to #### characters.
	 */
	public static function removeProfanity(string $message, array $words) : string {
		$replacementCharacter = Loader::getInstance()->getConfig()->get("replacementCharacter");
		foreach ($words as $profanity) {
			$message = preg_replace("/" . $profanity . "/i", str_repeat($replacementCharacter, mb_strlen($profanity, "utf8")), $message);
		}
		/**
		 * Control the ASCII Unicode Bypassing
		 *
		 * We are expectate using unicode characters can bypass profanity...
		 */
		$removeUnicode = (bool) Loader::getInstance()->getConfig()->get("removeUnicode");
		if ($removeUnicode) {
			$message = preg_replace("/[[:^print:]]/", "", $message);
		}
		return $message;
	}

	/**
	 * Returns array batch in english default profanity.
	 */
	public static function defaultProfanity() : array {
		return $words = [
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
	}
}
