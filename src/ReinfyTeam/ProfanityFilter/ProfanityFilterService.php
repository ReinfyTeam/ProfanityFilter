<?php declare(strict_types=1);

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

namespace ReinfyTeam\ProfanityFilter;

use Exception;
use RuntimeException;
use function mb_strlen;
use function preg_match;
use function preg_replace;
use function str_repeat;
use function str_replace;
use function strlen;

final class ProfanityFilterService {
	public const UNICODE_BLOCK_ALL = 0;
	public const UNICODE_BLOCK_LETTERS = 1;
	public const UNICODE_BLOCK_PUNCTUATION = 2;
	public const UNICODE_BLOCK_SYMBOLS = 3;
	public const UNICODE_BLOCK_CONTROL = 4;

	public const DEFAULT_REPLACEMENT_CHARACTER = "#";
	private const REPLACEMENT_LENGTH = 1;

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
	 * Whether to detect message on provided words.
	 */
	public static function containsProfanity(string $message, array $words) : bool {
		foreach ($words as $pattern) {
			if (preg_match("/" . $pattern . "/iu", $message) > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * It is being used to remove profanities on message.
	 * Returns string convert to #### characters.
	 */
	public static function maskProfanity(string $message, array $words, string $replacementCharacter = self::DEFAULT_REPLACEMENT_CHARACTER) : string {
		if (strlen($replacementCharacter) !== self::REPLACEMENT_LENGTH) {
			throw new Exception("Replacement character must be exactly one character long.");
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
	public static function sanitizeUnicode(string $text, int $blockType = self::UNICODE_BLOCK_LETTERS, bool $useMultibyteLength = true) : string {
		if ($useMultibyteLength) {
			$text = mb_strlen($text, "utf8");
		}

		return match($blockType) {
			self::UNICODE_BLOCK_LETTERS => self::applyUnicodeBlock1($text),
			self::UNICODE_BLOCK_PUNCTUATION => self::applyUnicodeBlock2($text),
			self::UNICODE_BLOCK_SYMBOLS => self::applyUnicodeBlock3($text),
			self::UNICODE_BLOCK_CONTROL => self::applyUnicodeBlock4($text),
			self::UNICODE_BLOCK_ALL => self::applyAllUnicodeBlocks($text),
			default => throw new RuntimeException("Unable to read properties of " . $blockType . ", because the id could'nt be found. Check your configuration if it is correct."),
		};
	}

	private static function applyUnicodeBlock1(string $text) : string {
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
		return preg_replace("/[ζ]/u", "Z", $text);
	}

	private static function applyUnicodeBlock2(string $text) : string {
		$text = preg_replace("/[‚‚]/u", ",", $text);
		$text = preg_replace("/[`‛′’‘]/u", "'", $text);
		$text = preg_replace("/[″“”«»„]/u", '"', $text);
		$text = preg_replace("/[—–―−–‾⌐─↔→←]/u", '-', $text);
		$text = preg_replace("/[  ]/u", ' ', $text);

		$text = str_replace("…", "...", $text);
		$text = str_replace("≠", "!=", $text);
		$text = str_replace("≤", "<=", $text);
		$text = str_replace("≥", ">=", $text);
		return preg_replace("/[‗≈≡]/u", "=", $text);
	}

	private static function applyUnicodeBlock3(string $text) : string {
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
		return preg_replace("/[∞∩∫⌂⌠⌡]/u", "", $text);
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

	public static function getDefaultProfanityList() : array {
		return self::DEFAULT_PROFANITY_WORDS;
	}
}



