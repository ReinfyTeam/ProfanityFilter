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

namespace ReinfyTeam\ProfanityFilter;

use Exception;
use RuntimeException;
use function mb_strlen;
use function preg_match;
use function preg_replace;
use function sizeof;
use function str_repeat;
use function str_replace;
use function strlen;

final class PluginAPI {
	/**
	 * Whether to detect message on provided words.
	 */
	public static function detectProfanity(string $message, array $words) : bool {
		$filterCount = sizeof($words);
		for ($i = 0; $i < $filterCount; $i++) {
			if (preg_match("/" . $words[$i] . "/iu", $message) > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * It is being used to remove profanities on message.
	 * Returns string convert to #### characters.
	 */
	public static function removeProfanity(string $message, array $words, string $replacementCharacter = "#") : string {
		if (strlen($replacementCharacter) < 1) {
			throw new Exception("Cannot replace character longer than 1 character.");
		}
		foreach ($words as $profanity) {
			$message = preg_replace("/" . $profanity . "/i", str_repeat($replacementCharacter, mb_strlen($profanity)), $message);
		}
		return $message;
	}

	/**
	 * Remove Unicodes and Other Non-Printable ASCII Characters from text.
	 *
	 * TODO: Improve this blocking in future...
	 * Changelogs:
	 * - Improved in v0.0.4-BETA
	 * - Block type and mb_strlen() was introduced in v0.0.8
	 * -
	 */
	public static function removeUnicode(string $text, int $block_type = 1, bool $mb_strlen = true) : string {
		if ($mb_strlen) {
			$text = mb_strlen($text, "utf8");
		}

		return match($block_type) {
			1 => self::applyUnicodeBlock1($text),
			2 => self::applyUnicodeBlock2($text),
			3 => self::applyUnicodeBlock3($text),
			4 => self::applyUnicodeBlock4($text),
			0 => self::applyAllUnicodeBlocks($text),
			default => throw new RuntimeException("Unable to read properties of " . $block_type . ", because the id could'nt be found. Check your configuration if it is correct."),
		};
	}

	private static function applyUnicodeBlock1(string $text) : string {
		$text = preg_replace("/[âˆ‚Î¬Î±ï„ƒÃ¡Ã Ã¢Ã£ÂªÃ¤]/u", "a", $text);
		$text = preg_replace("/[âˆ†Ð»Ð”Î›Ð´ÐÃÃ€Ã‚ÃƒÃ„]/u", "A", $text);
		$text = preg_replace("/[Ð‚ÐªÐ¬Ð‘ÑŠÑŒ]/u", "b", $text);
		$text = preg_replace("/[Î²Ð²Ð’]/u", "B", $text);
		$text = preg_replace("/[Ã§Ï‚Â©Ñ]/u", "c", $text);
		$text = preg_replace("/[Ã‡Ð¡]/u", "C", $text);
		$text = preg_replace("/[Î´ï„„]/u", "d", $text);
		$text = preg_replace("/[Ã©Ã¨ÃªÃ«Î­Ã«Ã¨Îµï„…Ðµâ„®Ñ‘Ñ”ÑÐ­]/u", "e", $text);
		$text = preg_replace("/[Ã‰ÃˆÃŠÃ‹â‚¬Î¾Ð„â‚¬Ð•âˆ‘]/u", "E", $text);
		$text = preg_replace("/[â‚£]/u", "F", $text);
		$text = preg_replace("/[ÐÐ½ÐŠÑš]/u", "H", $text);
		$text = preg_replace("/[Ñ’Ñ›Ð‹]/u", "h", $text);
		$text = preg_replace("/[ÃÃŒÃŽÃ]/u", "I", $text);
		$text = preg_replace("/[Ã­Ã¬Ã®Ã¯Î¹Î¯ÏŠÑ–]/u", "i", $text);
		$text = preg_replace("/[ÐˆÑ˜]/u", "j", $text);
		$text = preg_replace("/[ÎšÐŒÐš]/u", 'K', $text);
		$text = preg_replace("/[ÑœÐº]/u", 'k', $text);
		$text = preg_replace("/[â„“âˆŸ]/u", 'l', $text);
		$text = preg_replace("/[ÐœÐ¼]/u", "M", $text);
		$text = preg_replace("/[Ã±Î·Î®Î·Ï€â¿]/u", "n", $text);
		$text = preg_replace("/[Ã‘âˆÐ¿ÐŸÐ˜Ð™Ð¸Ð¹ÎÐ›]/u", "N", $text);
		$text = preg_replace("/[Ã³Ã²Ã´ÃµÂºÃ¶Î¿ï„†ï„ˆÐ¤ÏƒÏŒÐ¾]/u", "o", $text);
		$text = preg_replace("/[Ã“Ã’Ã”Ã•Ã–Î¸Î©Î¸Ðžâ„¦]/u", "O", $text);
		$text = preg_replace("/[ÏÏ†Ñ€Ð Ñ„]/u", "p", $text);
		$text = preg_replace("/[Â®ÑÐ¯]/u", "R", $text);
		$text = preg_replace("/[Ð“ÐƒÐ³Ñ“]/u", "r", $text);
		$text = preg_replace("/[Ð…]/u", "S", $text);
		$text = preg_replace("/[Ñ•]/u","s", $text);
		$text = preg_replace("/[Ð¢Ñ‚]/u", "T", $text);
		$text = preg_replace("/[Ï„â€ â€¡]/u", "t", $text);
		$text = preg_replace("/[ÃºÃ¹Ã»Ã¼ÑŸÎ¼Î°ÂµÏ…Ï‹Ï]/u", "u", $text);
		$text = preg_replace("/[âˆš]/u", "v", $text);
		$text = preg_replace("/[ÃšÃ™Ã›ÃœÐÐ¦Ñ†]/u", "U", $text);
		$text = preg_replace("/[Î¨ÏˆÏ‰ÏŽáº…áºƒáºÑ‰Ñˆï„‡]/u", "w", $text);
		$text = preg_replace("/[áº€áº„áº‚Ð¨Ð©]/u", "W", $text);
		$text = preg_replace("/[Î§Ï‡Ð–Ð¥Ð¶]/u", "x", $text);
		$text = preg_replace("/[á»²Î«Â¥]/u", "Y", $text);
		$text = preg_replace("/[á»³Î³ÑžÐŽÐ£ÑƒÑ‡]/u", "y", $text);
		return preg_replace("/[Î¶]/u", "Z", $text);
	}

	private static function applyUnicodeBlock2(string $text) : string {
		$text = preg_replace("/[â€šâ€šï€„ï€…]/u", ",", $text);
		$text = preg_replace("/[`â€›â€²â€™â€˜]/u", "'", $text);
		$text = preg_replace("/[â€³â€œâ€Â«Â»â€ž]/u", '"', $text);
		$text = preg_replace("/[â€”â€“â€•âˆ’â€“â€¾âŒâ”€â†”â†’â†]/u", '-', $text);
		$text = preg_replace("/[  ]/u", ' ', $text);

		$text = str_replace("â€¦", "...", $text);
		$text = str_replace("â‰ ", "!=", $text);
		$text = str_replace("â‰¤", "<=", $text);
		$text = str_replace("â‰¥", ">=", $text);
		return preg_replace("/[â€—â‰ˆâ‰¡]/u", "=", $text);
	}

	private static function applyUnicodeBlock3(string $text) : string {
		$text = str_replace("Ñ‹Ð«", "bl", $text);
		$text = str_replace("â„…", "c/o", $text);
		$text = str_replace("â‚§", "Pts", $text);
		$text = str_replace("â„¢", "tm", $text);
		$text = str_replace("â„–", "No", $text);
		$text = str_replace("Ð§", "4", $text);
		$text = str_replace("â€°", "%", $text);
		$text = preg_replace("/[âˆ™â€¢]/u", "*", $text);
		$text = str_replace("â€¹", "<", $text);
		$text = str_replace("â€º", ">", $text);
		$text = str_replace("â€¼", "!!", $text);
		$text = str_replace("â„", "/", $text);
		$text = str_replace("âˆ•", "/", $text);
		$text = str_replace("â…ž", "7/8", $text);
		$text = str_replace("â…", "5/8", $text);
		$text = str_replace("â…œ", "3/8", $text);
		$text = str_replace("â…›", "1/8", $text);
		$text = preg_replace("/[â€°]/u", "%", $text);
		$text = preg_replace("/[Ð‰Ñ™]/u", "Ab", $text);
		$text = preg_replace("/[Ð®ÑŽ]/u", "IO", $text);
		$text = preg_replace("/[ï¬ï¬‚ï€ï€‚]/u", "fi", $text);
		$text = preg_replace("/[Ð·Ð—]/u", "3", $text);
		$text = str_replace("Â£", "(pounds)", $text);
		$text = str_replace("â‚¤", "(lira)", $text);
		$text = preg_replace("/[â€°]/u", "%", $text);
		$text = preg_replace("/[â†¨â†•â†“â†‘â”‚]/u", "|", $text);
		return preg_replace("/[âˆžâˆ©âˆ«âŒ‚âŒ âŒ¡]/u", "", $text);
	}

	private static function applyUnicodeBlock4(string $text) : string {
		return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
	}

	private static function applyAllUnicodeBlocks(string $text) : string {
		$text = self::applyUnicodeBlock1($text);
		$text = self::applyUnicodeBlock2($text);
		$text = self::applyUnicodeBlock3($text);
		return self::applyUnicodeBlock4($text);
	}

	public static function defaultProfanity() : array {
		return [
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


