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

use RuntimeException;
use function preg_replace;
use function str_replace;

final class UnicodeSanitizer {
	public const BLOCK_ALL = 0;
	public const BLOCK_LETTERS = 1;
	public const BLOCK_PUNCTUATION = 2;
	public const BLOCK_SYMBOLS = 3;
	public const BLOCK_CONTROL = 4;

	public static function sanitizeUnicode(string $text, int $blockType = self::BLOCK_LETTERS, bool $useMultibyteLength = true) : string {
		return match ($blockType) {
			self::BLOCK_LETTERS => self::applyUnicodeBlock1($text),
			self::BLOCK_PUNCTUATION => self::applyUnicodeBlock2($text),
			self::BLOCK_SYMBOLS => self::applyUnicodeBlock3($text),
			self::BLOCK_CONTROL => self::applyUnicodeBlock4($text),
			self::BLOCK_ALL => self::applyAllUnicodeBlocks($text),
			default => throw new RuntimeException("Unable to read properties of " . $blockType . ", because the id could'nt be found. Check your configuration if it is correct."),
		};
	}

	private static function applyUnicodeBlock1(string $text) : string {
		$text = (string) preg_replace("/[Ã¢Ë†â€šÃŽÂ¬ÃŽÂ±Ã¯â€žÆ’ÃƒÂ¡ÃƒÂ ÃƒÂ¢ÃƒÂ£Ã‚ÂªÃƒÂ¤]/u", "a", $text);
		$text = (string) preg_replace("/[Ã¢Ë†â€ ÃÂ»Ãâ€ÃŽâ€ºÃÂ´ÃÂÃƒÂÃƒâ‚¬Ãƒâ€šÃƒÆ’Ãƒâ€ž]/u", "A", $text);
		$text = (string) preg_replace("/[Ãâ€šÃÂªÃÂ¬Ãâ€˜Ã‘Å Ã‘Å’]/u", "b", $text);
		$text = (string) preg_replace("/[ÃŽÂ²ÃÂ²Ãâ€™]/u", "B", $text);
		$text = (string) preg_replace("/[ÃƒÂ§Ãâ€šÃ‚Â©Ã‘Â]/u", "c", $text);
		$text = (string) preg_replace("/[Ãƒâ€¡ÃÂ¡]/u", "C", $text);
		$text = (string) preg_replace("/[ÃŽÂ´Ã¯â€žâ€ž]/u", "d", $text);
		$text = (string) preg_replace("/[ÃƒÂ©ÃƒÂ¨ÃƒÂªÃƒÂ«ÃŽÂ­ÃƒÂ«ÃƒÂ¨ÃŽÂµÃ¯â€žâ€¦ÃÂµÃ¢â€žÂ®Ã‘â€˜Ã‘â€Ã‘ÂÃÂ­]/u", "e", $text);
		$text = (string) preg_replace("/[Ãƒâ€°ÃƒË†ÃƒÅ Ãƒâ€¹Ã¢â€šÂ¬ÃŽÂ¾Ãâ€žÃ¢â€šÂ¬Ãâ€¢Ã¢Ë†â€˜]/u", "E", $text);
		$text = (string) preg_replace("/[Ã¢â€šÂ£]/u", "F", $text);
		$text = (string) preg_replace("/[ÃÂÃÂ½ÃÅ Ã‘Å¡]/u", "H", $text);
		$text = (string) preg_replace("/[Ã‘â€™Ã‘â€ºÃâ€¹]/u", "h", $text);
		$text = (string) preg_replace("/[ÃƒÂÃƒÅ’ÃƒÅ½ÃƒÂ]/u", "I", $text);
		$text = (string) preg_replace("/[ÃƒÂ­ÃƒÂ¬ÃƒÂ®ÃƒÂ¯ÃŽÂ¹ÃŽÂ¯ÃÅ Ã‘â€“]/u", "i", $text);
		$text = (string) preg_replace("/[ÃË†Ã‘Ëœ]/u", "j", $text);
		$text = (string) preg_replace("/[ÃŽÅ¡ÃÅ’ÃÅ¡]/u", 'K', $text);
		$text = (string) preg_replace("/[Ã‘Å“ÃÂº]/u", 'k', $text);
		$text = (string) preg_replace("/[Ã¢â€žâ€œÃ¢Ë†Å¸]/u", 'l', $text);
		$text = (string) preg_replace("/[ÃÅ“ÃÂ¼]/u", "M", $text);
		$text = (string) preg_replace("/[ÃƒÂ±ÃŽÂ·ÃŽÂ®ÃŽÂ·Ãâ‚¬Ã¢ÂÂ¿]/u", "n", $text);
		$text = (string) preg_replace("/[Ãƒâ€˜Ã¢Ë†ÂÃÂ¿ÃÅ¸ÃËœÃâ„¢ÃÂ¸ÃÂ¹ÃŽÂÃâ€º]/u", "N", $text);
		$text = (string) preg_replace("/[ÃƒÂ³ÃƒÂ²ÃƒÂ´ÃƒÂµÃ‚ÂºÃƒÂ¶ÃŽÂ¿Ã¯â€žâ€ Ã¯â€žË†ÃÂ¤ÃÆ’ÃÅ’ÃÂ¾]/u", "o", $text);
		$text = (string) preg_replace("/[Ãƒâ€œÃƒâ€™Ãƒâ€Ãƒâ€¢Ãƒâ€“ÃŽÂ¸ÃŽÂ©ÃŽÂ¸ÃÅ¾Ã¢â€žÂ¦]/u", "O", $text);
		$text = (string) preg_replace("/[ÃÂÃâ€ Ã‘â‚¬ÃÂ Ã‘â€ž]/u", "p", $text);
		$text = (string) preg_replace("/[Ã‚Â®Ã‘ÂÃÂ¯]/u", "R", $text);
		$text = (string) preg_replace("/[Ãâ€œÃÆ’ÃÂ³Ã‘â€œ]/u", "r", $text);
		$text = (string) preg_replace("/[Ãâ€¦]/u", "S", $text);
		$text = (string) preg_replace("/[Ã‘â€¢]/u","s", $text);
		$text = (string) preg_replace("/[ÃÂ¢Ã‘â€š]/u", "T", $text);
		$text = (string) preg_replace("/[Ãâ€žÃ¢â‚¬Â Ã¢â‚¬Â¡]/u", "t", $text);
		$text = (string) preg_replace("/[ÃƒÂºÃƒÂ¹ÃƒÂ»ÃƒÂ¼Ã‘Å¸ÃŽÂ¼ÃŽÂ°Ã‚ÂµÃâ€¦Ãâ€¹ÃÂ]/u", "u", $text);
		$text = (string) preg_replace("/[Ã¢Ë†Å¡]/u", "v", $text);
		$text = (string) preg_replace("/[ÃƒÅ¡Ãƒâ„¢Ãƒâ€ºÃƒÅ“ÃÂÃÂ¦Ã‘â€ ]/u", "U", $text);
		$text = (string) preg_replace("/[ÃŽÂ¨ÃË†Ãâ€°ÃÅ½Ã¡Âºâ€¦Ã¡ÂºÆ’Ã¡ÂºÂÃ‘â€°Ã‘Ë†Ã¯â€žâ€¡]/u", "w", $text);
		$text = (string) preg_replace("/[Ã¡Âºâ‚¬Ã¡Âºâ€žÃ¡Âºâ€šÃÂ¨ÃÂ©]/u", "W", $text);
		$text = (string) preg_replace("/[ÃŽÂ§Ãâ€¡Ãâ€“ÃÂ¥ÃÂ¶]/u", "x", $text);
		$text = (string) preg_replace("/[Ã¡Â»Â²ÃŽÂ«Ã‚Â¥]/u", "Y", $text);
		$text = (string) preg_replace("/[Ã¡Â»Â³ÃŽÂ³Ã‘Å¾ÃÅ½ÃÂ£Ã‘Æ’Ã‘â€¡]/u", "y", $text);
		return (string) preg_replace("/[ÃŽÂ¶]/u", "Z", $text);
	}

	private static function applyUnicodeBlock2(string $text) : string {
		$text = (string) preg_replace("/[Ã¢â‚¬Å¡Ã¢â‚¬Å¡Ã¯â‚¬â€žÃ¯â‚¬â€¦]/u", ",", $text);
		$text = (string) preg_replace("/[`Ã¢â‚¬â€ºÃ¢â‚¬Â²Ã¢â‚¬â„¢Ã¢â‚¬Ëœ]/u", "'", $text);
		$text = (string) preg_replace("/[Ã¢â‚¬Â³Ã¢â‚¬Å“Ã¢â‚¬ÂÃ‚Â«Ã‚Â»Ã¢â‚¬Å¾]/u", '"', $text);
		$text = (string) preg_replace("/[Ã¢â‚¬â€Ã¢â‚¬â€œÃ¢â‚¬â€¢Ã¢Ë†â€™Ã¢â‚¬â€œÃ¢â‚¬Â¾Ã¢Å’ÂÃ¢â€â‚¬Ã¢â€ â€Ã¢â€ â€™Ã¢â€ Â]/u", '-', $text);
		$text = (string) preg_replace("/[  ]/u", ' ', $text);

		$text = str_replace("Ã¢â‚¬Â¦", "...", $text);
		$text = str_replace("Ã¢â€°Â ", "!=", $text);
		$text = str_replace("Ã¢â€°Â¤", "<=", $text);
		$text = str_replace("Ã¢â€°Â¥", ">=", $text);
		return (string) preg_replace("/[Ã¢â‚¬â€”Ã¢â€°Ë†Ã¢â€°Â¡]/u", "=", $text);
	}

	private static function applyUnicodeBlock3(string $text) : string {
		$text = str_replace("Ã‘â€¹ÃÂ«", "bl", $text);
		$text = str_replace("Ã¢â€žâ€¦", "c/o", $text);
		$text = str_replace("Ã¢â€šÂ§", "Pts", $text);
		$text = str_replace("Ã¢â€žÂ¢", "tm", $text);
		$text = str_replace("Ã¢â€žâ€“", "No", $text);
		$text = str_replace("ÃÂ§", "4", $text);
		$text = str_replace("Ã¢â‚¬Â°", "%", $text);
		$text = (string) preg_replace("/[Ã¢Ë†â„¢Ã¢â‚¬Â¢]/u", "*", $text);
		$text = str_replace("Ã¢â‚¬Â¹", "<", $text);
		$text = str_replace("Ã¢â‚¬Âº", ">", $text);
		$text = str_replace("Ã¢â‚¬Â¼", "!!", $text);
		$text = str_replace("Ã¢Ââ€ž", "/", $text);
		$text = str_replace("Ã¢Ë†â€¢", "/", $text);
		$text = str_replace("Ã¢â€¦Å¾", "7/8", $text);
		$text = str_replace("Ã¢â€¦Â", "5/8", $text);
		$text = str_replace("Ã¢â€¦Å“", "3/8", $text);
		$text = str_replace("Ã¢â€¦â€º", "1/8", $text);
		$text = (string) preg_replace("/[Ã¢â‚¬Â°]/u", "%", $text);
		$text = (string) preg_replace("/[Ãâ€°Ã‘â„¢]/u", "Ab", $text);
		$text = (string) preg_replace("/[ÃÂ®Ã‘Å½]/u", "IO", $text);
		$text = (string) preg_replace("/[Ã¯Â¬ÂÃ¯Â¬â€šÃ¯â‚¬ÂÃ¯â‚¬â€š]/u", "fi", $text);
		$text = (string) preg_replace("/[ÃÂ·Ãâ€”]/u", "3", $text);
		$text = str_replace("Ã‚Â£", "(pounds)", $text);
		$text = str_replace("Ã¢â€šÂ¤", "(lira)", $text);
		$text = (string) preg_replace("/[Ã¢â‚¬Â°]/u", "%", $text);
		$text = (string) preg_replace("/[Ã¢â€ Â¨Ã¢â€ â€¢Ã¢â€ â€œÃ¢â€ â€˜Ã¢â€â€š]/u", "|", $text);
		return (string) preg_replace("/[Ã¢Ë†Å¾Ã¢Ë†Â©Ã¢Ë†Â«Ã¢Å’â€šÃ¢Å’Â Ã¢Å’Â¡]/u", "", $text);
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
}
