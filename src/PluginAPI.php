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
	public static function removeProfanity(string $message, array $words, string $replacementCharacter = "#") : string {
		if (strlen($replacementCharacter) < 1) {
			throw new Exception("Cannot replace character longer than 1 character.");
		}
		foreach ($words as $profanity) {
			$message = preg_replace("/" . $profanity . "/i", str_repeat($replacementCharacter, mb_strlen($profanity, "utf8")), $message);
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

		switch($block_type) {
			case 1:
				// Single Characters
				$text = preg_replace("/[∂άαáàâãªä]/u", "a", $text);
				$text = preg_replace("/[∆лДΛдАÁÀÂÃÄ]/u", "A", $text);
				$text = preg_replace("/[ЂЪЬБъь]/u", "b", $text);
				$text = preg_replace("/[βвВ]/u", "B", $text);
				$text = preg_replace("/[çς©с]/u", "c", $text);
				$text = preg_replace("/[ÇС]/u", "C", $text);
				$text = preg_replace("/[δ]/u", "d", $text);
				$text = preg_replace("/[éèêëέëèεе℮ёєэЭ]/u", "e", $text);
				$text = preg_replace("/[ÉÈÊË€ξЄ€Е∑]/u", "E", $text);
				$text = preg_replace("/[₣]/u", "F", $text);
				$text = preg_replace("/[НнЊњ]/u", "H", $text);
				$text = preg_replace("/[ђћЋ]/u", "h", $text);
				$text = preg_replace("/[ÍÌÎÏ]/u", "I", $text);
				$text = preg_replace("/[íìîïιίϊі]/u", "i", $text);
				$text = preg_replace("/[Јј]/u", "j", $text);
				$text = preg_replace("/[ΚЌК]/u", 'K', $text);
				$text = preg_replace("/[ќк]/u", 'k', $text);
				$text = preg_replace("/[ℓ∟]/u", 'l', $text);
				$text = preg_replace("/[Мм]/u", "M", $text);
				$text = preg_replace("/[ñηήηπⁿ]/u", "n", $text);
				$text = preg_replace("/[Ñ∏пПИЙийΝЛ]/u", "N", $text);
				$text = preg_replace("/[óòôõºöοФσόо]/u", "o", $text);
				$text = preg_replace("/[ÓÒÔÕÖθΩθОΩ]/u", "O", $text);
				$text = preg_replace("/[ρφрРф]/u", "p", $text);
				$text = preg_replace("/[®яЯ]/u", "R", $text);
				$text = preg_replace("/[ГЃгѓ]/u", "r", $text);
				$text = preg_replace("/[Ѕ]/u", "S", $text);
				$text = preg_replace("/[ѕ]/u","s", $text);
				$text = preg_replace("/[Тт]/u", "T", $text);
				$text = preg_replace("/[τ†‡]/u", "t", $text);
				$text = preg_replace("/[úùûüџμΰµυϋύ]/u", "u", $text);
				$text = preg_replace("/[√]/u", "v", $text);
				$text = preg_replace("/[ÚÙÛÜЏЦц]/u", "U", $text);
				$text = preg_replace("/[Ψψωώẅẃẁщш]/u", "w", $text);
				$text = preg_replace("/[ẀẄẂШЩ]/u", "W", $text);
				$text = preg_replace("/[ΧχЖХж]/u", "x", $text);
				$text = preg_replace("/[ỲΫ¥]/u", "Y", $text);
				$text = preg_replace("/[ỳγўЎУуч]/u", "y", $text);
				$text = preg_replace("/[ζ]/u", "Z", $text);
				break;
			case 2:
				// Punctuation
				$text = preg_replace("/[‚‚]/u", ",", $text);
				$text = preg_replace("/[`‛′’‘]/u", "'", $text);
				$text = preg_replace("/[″“”«»„]/u", '"', $text);
				$text = preg_replace("/[—–―−–‾⌐─↔→←]/u", '-', $text);
				$text = preg_replace("/[  ]/u", ' ', $text);

				$text = str_replace("…", "...", $text);
				$text = str_replace("≠", "!=", $text);
				$text = str_replace("≤", "<=", $text);
				$text = str_replace("≥", ">=", $text);
				$text = preg_replace("/[‗≈≡]/u", "=", $text);
				break;
			case 3:
				// Exciting combinations
				$text = str_replace("ыЫ", "bl", $text);
				$text = str_replace("℅", "c/o", $text);
				$text = str_replace("₧", "Pts", $text);
				$text = str_replace("™", "tm", $text);
				$text = str_replace("№", "No", $text);
				$text = str_replace("Ч", "4", $text);
				$text = str_replace("‰", "%", $text);
				$text = preg_replace("/[∙•]/u", "*", $text);
				$text = str_replace("‹", "<", $text);
				$text = str_replace("›", ">", $text);
				$text = str_replace("‼", "!!", $text);
				$text = str_replace("⁄", "/", $text);
				$text = str_replace("∕", "/", $text);
				$text = str_replace("⅞", "7/8", $text);
				$text = str_replace("⅝", "5/8", $text);
				$text = str_replace("⅜", "3/8", $text);
				$text = str_replace("⅛", "1/8", $text);
				$text = preg_replace("/[‰]/u", "%", $text);
				$text = preg_replace("/[Љљ]/u", "Ab", $text);
				$text = preg_replace("/[Юю]/u", "IO", $text);
				$text = preg_replace("/[ﬁﬂ]/u", "fi", $text);
				$text = preg_replace("/[зЗ]/u", "3", $text);
				$text = str_replace("£", "(pounds)", $text);
				$text = str_replace("₤", "(lira)", $text);
				$text = preg_replace("/[‰]/u", "%", $text);
				$text = preg_replace("/[↨↕↓↑│]/u", "|", $text);
				$text = preg_replace("/[∞∩∫⌂⌠⌡]/u", "", $text);
				break;
			case 4:
				// Remove Unicode Characters
				$text = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
				break;
			case 0:
				// Single Characters
				$text = preg_replace("/[∂άαáàâãªä]/u", "a", $text);
				$text = preg_replace("/[∆лДΛдАÁÀÂÃÄ]/u", "A", $text);
				$text = preg_replace("/[ЂЪЬБъь]/u", "b", $text);
				$text = preg_replace("/[βвВ]/u", "B", $text);
				$text = preg_replace("/[çς©с]/u", "c", $text);
				$text = preg_replace("/[ÇС]/u", "C", $text);
				$text = preg_replace("/[δ]/u", "d", $text);
				$text = preg_replace("/[éèêëέëèεе℮ёєэЭ]/u", "e", $text);
				$text = preg_replace("/[ÉÈÊË€ξЄ€Е∑]/u", "E", $text);
				$text = preg_replace("/[₣]/u", "F", $text);
				$text = preg_replace("/[НнЊњ]/u", "H", $text);
				$text = preg_replace("/[ђћЋ]/u", "h", $text);
				$text = preg_replace("/[ÍÌÎÏ]/u", "I", $text);
				$text = preg_replace("/[íìîïιίϊі]/u", "i", $text);
				$text = preg_replace("/[Јј]/u", "j", $text);
				$text = preg_replace("/[ΚЌК]/u", 'K', $text);
				$text = preg_replace("/[ќк]/u", 'k', $text);
				$text = preg_replace("/[ℓ∟]/u", 'l', $text);
				$text = preg_replace("/[Мм]/u", "M", $text);
				$text = preg_replace("/[ñηήηπⁿ]/u", "n", $text);
				$text = preg_replace("/[Ñ∏пПИЙийΝЛ]/u", "N", $text);
				$text = preg_replace("/[óòôõºöοФσόо]/u", "o", $text);
				$text = preg_replace("/[ÓÒÔÕÖθΩθОΩ]/u", "O", $text);
				$text = preg_replace("/[ρφрРф]/u", "p", $text);
				$text = preg_replace("/[®яЯ]/u", "R", $text);
				$text = preg_replace("/[ГЃгѓ]/u", "r", $text);
				$text = preg_replace("/[Ѕ]/u", "S", $text);
				$text = preg_replace("/[ѕ]/u","s", $text);
				$text = preg_replace("/[Тт]/u", "T", $text);
				$text = preg_replace("/[τ†‡]/u", "t", $text);
				$text = preg_replace("/[úùûüџμΰµυϋύ]/u", "u", $text);
				$text = preg_replace("/[√]/u", "v", $text);
				$text = preg_replace("/[ÚÙÛÜЏЦц]/u", "U", $text);
				$text = preg_replace("/[Ψψωώẅẃẁщш]/u", "w", $text);
				$text = preg_replace("/[ẀẄẂШЩ]/u", "W", $text);
				$text = preg_replace("/[ΧχЖХж]/u", "x", $text);
				$text = preg_replace("/[ỲΫ¥]/u", "Y", $text);
				$text = preg_replace("/[ỳγўЎУуч]/u", "y", $text);
				$text = preg_replace("/[ζ]/u", "Z", $text);

				// Punctuation
				$text = preg_replace("/[‚‚]/u", ",", $text);
				$text = preg_replace("/[`‛′’‘]/u", "'", $text);
				$text = preg_replace("/[″“”«»„]/u", '"', $text);
				$text = preg_replace("/[—–―−–‾⌐─↔→←]/u", '-', $text);
				$text = preg_replace("/[  ]/u", ' ', $text);

				$text = str_replace("…", "...", $text);
				$text = str_replace("≠", "!=", $text);
				$text = str_replace("≤", "<=", $text);
				$text = str_replace("≥", ">=", $text);
				$text = preg_replace("/[‗≈≡]/u", "=", $text);

				// Exciting combinations
				$text = str_replace("ыЫ", "bl", $text);
				$text = str_replace("℅", "c/o", $text);
				$text = str_replace("₧", "Pts", $text);
				$text = str_replace("™", "tm", $text);
				$text = str_replace("№", "No", $text);
				$text = str_replace("Ч", "4", $text);
				$text = str_replace("‰", "%", $text);
				$text = preg_replace("/[∙•]/u", "*", $text);
				$text = str_replace("‹", "<", $text);
				$text = str_replace("›", ">", $text);
				$text = str_replace("‼", "!!", $text);
				$text = str_replace("⁄", "/", $text);
				$text = str_replace("∕", "/", $text);
				$text = str_replace("⅞", "7/8", $text);
				$text = str_replace("⅝", "5/8", $text);
				$text = str_replace("⅜", "3/8", $text);
				$text = str_replace("⅛", "1/8", $text);
				$text = preg_replace("/[‰]/u", "%", $text);
				$text = preg_replace("/[Љљ]/u", "Ab", $text);
				$text = preg_replace("/[Юю]/u", "IO", $text);
				$text = preg_replace("/[ﬁﬂ]/u", "fi", $text);
				$text = preg_replace("/[зЗ]/u", "3", $text);
				$text = str_replace("£", "(pounds)", $text);
				$text = str_replace("₤", "(lira)", $text);
				$text = preg_replace("/[‰]/u", "%", $text);
				$text = preg_replace("/[↨↕↓↑│]/u", "|", $text);
				$text = preg_replace("/[∞∩∫⌂⌠⌡]/u", "", $text);

				// Remove Unicode Characters
				$text = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
				break;
			default:
				throw new \RuntimeException("Unable to read properties of " . $block_type . ", because the id could'nt be found. Check your configuration if its correct.");
		}
		return $text;
	}

	/**
	 * Returns array batch in english default profanity.
	 */
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
