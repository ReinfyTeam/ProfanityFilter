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

use Exception;
use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function implode;
use function is_string;
use function json_encode;
use function mb_strlen;
use function md5;
use function preg_match;
use function preg_quote;
use function preg_replace_callback;
use function str_repeat;
use function strlen;

final class ProfanityPatternCompiler {
	public const DEFAULT_REPLACEMENT_CHARACTER = "#";
	private const REPLACEMENT_LENGTH = 1;
	private const PATTERN_CACHE_LIMIT = 32;

	/** @var array<string, string> */
	private static array $patternCache = [];

	/**
	 * Compile the profanity list into a single regex and cache the result.
	 * Reduces repeated per-word regex compilation and keeps detection O(n) per message.
	 *
	 * @param string[] $words
	 */
	public static function containsProfanity(string $message, array $words) : bool {
		$pattern = self::getCompiledPattern($words);
		return $pattern !== null && preg_match($pattern, $message) === 1;
	}

	/**
	 * Mask profanity words with a replacement character.
	 *
	 * @param string[] $words
	 */
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
	 * @param string[] $words
	 */
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

		if (isset(self::$patternCache[$key])) {
			return self::$patternCache[$key];
		}

		$escaped = array_map(static fn(string $word) => preg_quote($word, "/"), $normalized);
		$pattern = "/(" . implode("|", $escaped) . ")/iu";

		// Keep cache size bounded to avoid unbounded growth when lists change at runtime.
		self::$patternCache[$key] = $pattern;
		if (count(self::$patternCache) > self::PATTERN_CACHE_LIMIT) {
			array_shift(self::$patternCache);
		}

		return $pattern;
	}
}
