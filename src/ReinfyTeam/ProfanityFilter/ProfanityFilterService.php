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

use ReinfyTeam\ProfanityFilter\Utils\ProfanityPatternCompiler;
use ReinfyTeam\ProfanityFilter\Utils\ProfanityWordList;
use ReinfyTeam\ProfanityFilter\Utils\UnicodeSanitizer;

final class ProfanityFilterService {
	public const UNICODE_BLOCK_ALL = UnicodeSanitizer::BLOCK_ALL;
	public const UNICODE_BLOCK_LETTERS = UnicodeSanitizer::BLOCK_LETTERS;
	public const UNICODE_BLOCK_PUNCTUATION = UnicodeSanitizer::BLOCK_PUNCTUATION;
	public const UNICODE_BLOCK_SYMBOLS = UnicodeSanitizer::BLOCK_SYMBOLS;
	public const UNICODE_BLOCK_CONTROL = UnicodeSanitizer::BLOCK_CONTROL;

	public const DEFAULT_REPLACEMENT_CHARACTER = ProfanityPatternCompiler::DEFAULT_REPLACEMENT_CHARACTER;

	/**
	 * Whether to detect message on provided words.
	 *
	 * @param string[] $words
	 */
	public static function containsProfanity(string $message, array $words) : bool {
		return ProfanityPatternCompiler::containsProfanity($message, $words);
	}

	/**
	 * It is being used to remove profanities on message.
	 * Returns string converted to #### characters.
	 *
	 * @param string[] $words
	 */
	public static function maskProfanity(string $message, array $words, string $replacementCharacter = self::DEFAULT_REPLACEMENT_CHARACTER) : string {
		return ProfanityPatternCompiler::maskProfanity($message, $words, $replacementCharacter);
	}

	/**
	 * Remove Unicodes and other Non-Printable ASCII Characters from text.
	 */
	public static function sanitizeUnicode(string $text, int $blockType = self::UNICODE_BLOCK_LETTERS, bool $useMultibyteLength = true) : string {
		return UnicodeSanitizer::sanitizeUnicode($text, $blockType, $useMultibyteLength);
	}

	/**
	 * @return string[]
	 */
	public static function getDefaultProfanityList() : array {
		return ProfanityWordList::getDefaultList();
	}
}
