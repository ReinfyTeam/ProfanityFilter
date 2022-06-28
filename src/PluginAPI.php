<?php

/*  					
 *					   _
 * 					  | |                  
 * __  ____ ___      _| |___  _____  _ __  
 * \ \/ / _` \ \ /\ / / __\ \/ / _ \| '_ \ 
 *  >  < (_| |\ V  V /| |_ >  < (_) | | | |
 * /_/\_\__, | \_/\_/  \__/_/\_\___/|_| |_|
 *         | |                             
 *         |_|                             
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
*/

namespace ProfanityFilter;

use function preg_match;
use function str_replace;
use function str_word_count;
use function str_repeat;
use function sizeof;

final class PluginAPI {
    
    /*
     * Whether to detect message on provided words.
     * @param string $message
     * @param array $words
     * @return bool
    */
    public static function detectProfanity(string $message, array $words) : bool {
		$filterCount = sizeof($words);
		for ($i = 0; $i < $filterCount; $i++) {
			$condition = preg_match('/' . $words[$i] . '/iu', $message) > 0;
			if ($condition) {
				return true;
			}
		}
		return false;
    }
    
    
    /*
     * It is being used to remove profanities on message.
     * Retuns string convert to **** characters.
     * @param string $message
     * @param array $words
     * @return string
    */
    
    public static function removeProfanity(string $message, $words) : string {
        foreach($words as $profanity){
            $message = str_replace($profanity, str_repeat("*", str_word_count($profanity)), $words); // best method ive maked
        }
        
        return $message;
    }
    
    /*
     * Returns array batch in english default profanity.
     * @return array
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