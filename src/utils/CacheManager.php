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
 * @author xqwtxib
 * @link http://xqwtxon.ml/
 *
*/

declare(strict_types=1);

namespace xqwtxon\HiveProfanityFilter\utils;

use pocketmine\utils\Config;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\KickManager;

class CacheManager {
	
	public function __construct(){
		$this->plugin = Loader::getInstance();
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
		$this->kicker = new KickManager();
	}
	
	public function cache(){
		return new Config($this->config->getDataFolder() . "cache/violations.yml", Config::YAML);
	}
	
	public function saveCache(){
		return $this->cache()->save();
	}
	
	public function set(string $k, string|array|int $v) :mixed{ 
		return $this->cache()->set($k, $v);
	}
	
	public function get(string $k) :mixed{ 
		return $this->cache()->get($k);
	}
}