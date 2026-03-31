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
use function array_filter;
use function mb_strlen;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function preg_replace_callback;
use function json_encode;
use function array_shift;
use function count;
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
	private const PATTERN_CACHE_LIMIT = 32;

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
	/** @param string[] $words */
	public static function containsProfanity(string $message, array $words) : bool {
		$pattern = self::getCompiledPattern($words);
		return $pattern !== null && preg_match($pattern, $message) === 1;
	}

	/**
	 * It is being used to remove profanities on message.
	 * Returns string convert to #### characters.
	 */
	/** @param string[] $words */
	public static function maskProfanity(string $message, array $words, string $replacementCharacter = self::DEFAULT_REPLACEMENT_CHARACTER) : string {
		if (strlen($replacementCharacter) !== self::REPLACEMENT_LENGTH) {
			throw new Exception("Replacement character must be exactly one character long.");
		}
		$pattern = self::getCompiledPattern($words);
		if ($pattern === null) {
			return $message;
		}

		$result = preg_replace_callback($pattern, static fn(array $match) => str_repeat($replacementCharacter, mb_strlen($match[0])), $message);
		return is_string($result) ? $result : $message;
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
		$text = (string) preg_replace("/[âˆ‚Î¬Î±ï„ƒÃ¡Ã Ã¢Ã£ÂªÃ¤]/u", "a", $text);
		$text = (string) preg_replace("/[âˆ†Ð»Ð”Î›Ð´ÐÃÃ€Ã‚ÃƒÃ„]/u", "A", $text);
		$text = (string) preg_replace("/[Ð‚ÐªÐ¬Ð‘ÑŠÑŒ]/u", "b", $text);
		$text = (string) preg_replace("/[Î²Ð²Ð’]/u", "B", $text);
		$text = (string) preg_replace("/[Ã§Ï‚Â©Ñ]/u", "c", $text);
		$text = (string) preg_replace("/[Ã‡Ð¡]/u", "C", $text);
		$text = (string) preg_replace("/[Î´ï„„]/u", "d", $text);
		$text = (string) preg_replace("/[Ã©Ã¨ÃªÃ«Î­Ã«Ã¨Îµï„…Ðµâ„®Ñ‘Ñ”ÑÐ­]/u", "e", $text);
		$text = (string) preg_replace("/[Ã‰ÃˆÃŠÃ‹â‚¬Î¾Ð„â‚¬Ð•âˆ‘]/u", "E", $text);
		$text = (string) preg_replace("/[â‚£]/u", "F", $text);
		$text = (string) preg_replace("/[ÐÐ½ÐŠÑš]/u", "H", $text);
		$text = (string) preg_replace("/[Ñ’Ñ›Ð‹]/u", "h", $text);
		$text = (string) preg_replace("/[ÃÃŒÃŽÃ]/u", "I", $text);
		$text = (string) preg_replace("/[Ã­Ã¬Ã®Ã¯Î¹Î¯ÏŠÑ–]/u", "i", $text);
		$text = (string) preg_replace("/[ÐˆÑ˜]/u", "j", $text);
		$text = (string) preg_replace("/[ÎšÐŒÐš]/u", 'K', $text);
		$text = (string) preg_replace("/[ÑœÐº]/u", 'k', $text);
		$text = (string) preg_replace("/[â„“âˆŸ]/u", 'l', $text);
		$text = (string) preg_replace("/[ÐœÐ¼]/u", "M", $text);
		$text = (string) preg_replace("/[Ã±Î·Î®Î·Ï€â¿]/u", "n", $text);
		$text = (string) preg_replace("/[Ã‘âˆÐ¿ÐŸÐ˜Ð™Ð¸Ð¹ÎÐ›]/u", "N", $text);
		$text = (string) preg_replace("/[Ã³Ã²Ã´ÃµÂºÃ¶Î¿ï„†ï„ˆÐ¤ÏƒÏŒÐ¾]/u", "o", $text);
		$text = (string) preg_replace("/[Ã“Ã’Ã”Ã•Ã–Î¸Î©Î¸Ðžâ„¦]/u", "O", $text);
		$text = (string) preg_replace("/[ÏÏ†Ñ€Ð Ñ„]/u", "p", $text);
		$text = (string) preg_replace("/[Â®ÑÐ¯]/u", "R", $text);
		$text = (string) preg_replace("/[Ð“ÐƒÐ³Ñ“]/u", "r", $text);
		$text = (string) preg_replace("/[Ð…]/u", "S", $text);
		$text = (string) preg_replace("/[Ñ•]/u","s", $text);
		$text = (string) preg_replace("/[Ð¢Ñ‚]/u", "T", $text);
		$text = (string) preg_replace("/[Ï„â€ â€¡]/u", "t", $text);
		$text = (string) preg_replace("/[ÃºÃ¹Ã»Ã¼ÑŸÎ¼Î°ÂµÏ…Ï‹Ï]/u", "u", $text);
		$text = (string) preg_replace("/[âˆš]/u", "v", $text);
		$text = (string) preg_replace("/[ÃšÃ™Ã›ÃœÐÐ¦Ñ†]/u", "U", $text);
		$text = (string) preg_replace("/[Î¨ÏˆÏ‰ÏŽáº…áºƒáºÑ‰Ñˆï„‡]/u", "w", $text);
		$text = (string) preg_replace("/[áº€áº„áº‚Ð¨Ð©]/u", "W", $text);
		$text = (string) preg_replace("/[Î§Ï‡Ð–Ð¥Ð¶]/u", "x", $text);
		$text = (string) preg_replace("/[á»²Î«Â¥]/u", "Y", $text);
		$text = (string) preg_replace("/[á»³Î³ÑžÐŽÐ£ÑƒÑ‡]/u", "y", $text);
		return (string) preg_replace("/[Î¶]/u", "Z", $text);
	}

	private static function applyUnicodeBlock2(string $text) : string {
		$text = (string) preg_replace("/[â€šâ€šï€„ï€…]/u", ",", $text);
		$text = (string) preg_replace("/[`â€›â€²â€™â€˜]/u", "'", $text);
		$text = (string) preg_replace("/[â€³â€œâ€Â«Â»â€ž]/u", '"', $text);
		$text = (string) preg_replace("/[â€”â€“â€•âˆ’â€“â€¾âŒâ”€â†”â†’â†]/u", '-', $text);
		$text = (string) preg_replace("/[  ]/u", ' ', $text);

		$text = str_replace("â€¦", "...", $text);
		$text = str_replace("â‰ ", "!=", $text);
		$text = str_replace("â‰¤", "<=", $text);
		$text = str_replace("â‰¥", ">=", $text);
		return (string) preg_replace("/[â€—â‰ˆâ‰¡]/u", "=", $text);
	}

	private static function applyUnicodeBlock3(string $text) : string {
		$text = str_replace("Ñ‹Ð«", "bl", $text);
		$text = str_replace("â„…", "c/o", $text);
		$text = str_replace("â‚§", "Pts", $text);
		$text = str_replace("â„¢", "tm", $text);
		$text = str_replace("â„–", "No", $text);
		$text = str_replace("Ð§", "4", $text);
		$text = str_replace("â€°", "%", $text);
		$text = (string) preg_replace("/[âˆ™â€¢]/u", "*", $text);
		$text = str_replace("â€¹", "<", $text);
		$text = str_replace("â€º", ">", $text);
		$text = str_replace("â€¼", "!!", $text);
		$text = str_replace("â„", "/", $text);
		$text = str_replace("âˆ•", "/", $text);
		$text = str_replace("â…ž", "7/8", $text);
		$text = str_replace("â…", "5/8", $text);
		$text = str_replace("â…œ", "3/8", $text);
		$text = str_replace("â…›", "1/8", $text);
		$text = (string) preg_replace("/[â€°]/u", "%", $text);
		$text = (string) preg_replace("/[Ð‰Ñ™]/u", "Ab", $text);
		$text = (string) preg_replace("/[Ð®ÑŽ]/u", "IO", $text);
		$text = (string) preg_replace("/[ï¬ï¬‚ï€ï€‚]/u", "fi", $text);
		$text = (string) preg_replace("/[Ð·Ð—]/u", "3", $text);
		$text = str_replace("Â£", "(pounds)", $text);
		$text = str_replace("â‚¤", "(lira)", $text);
		$text = (string) preg_replace("/[â€°]/u", "%", $text);
		$text = (string) preg_replace("/[â†¨â†•â†“â†‘â”‚]/u", "|", $text);
		return (string) preg_replace("/[âˆžâˆ©âˆ«âŒ‚âŒ âŒ¡]/u", "", $text);
	}

	private static function applyUnicodeBlock4(string $text) : string {
		return (string) preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
	}

	private static function applyAllUnicodeBlocks(string $text) : string {
		$text = self::applyUnicodeBlock1($text);
		$text = self::applyUnicodeBlock2($text);
		$text = self::applyUnicodeBlock3($text);
		return self::applyUnicodeBlock4($text);
	}

	/** @return string[] */
	public static function getDefaultProfanityList() : array {
		return self::DEFAULT_PROFANITY_WORDS;
	}

	/**
	 * Compile the profanity list into a single regex and cache the result.
	 * Reduces repeated per-word regex compilation and keeps detection O(n) per message.
	 */
	/** @param string[] $words */
	private static function getCompiledPattern(array $words) : ?string {
		$normalized = array_values(array_filter($words, static fn($word) => (string) $word !== ""));
		if ($normalized === []) {
			return null;
		}

		$json = json_encode($normalized);
		if ($json === false) {
			return null;
		}
		$key = md5($json);
		static $cache = [];

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$escaped = array_map(static fn(string $word) => preg_quote($word, "/"), $normalized);
		$pattern = "/(" . implode("|", $escaped) . ")/iu";

		// Keep cache size bounded to avoid unbounded growth when lists change at runtime.
		$cache[$key] = $pattern;
		if (count($cache) > self::PATTERN_CACHE_LIMIT) {
			array_shift($cache);
		}

		return $pattern;
	}
}



