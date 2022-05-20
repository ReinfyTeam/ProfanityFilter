<?php

namespace xqwtxon\HiveProfanityFilter\utils;

use pocketmine\utils\Config;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;

class CacheManager {
	public function cache(){
		return new Config($this->config->getDataFolder() . "cache/violations.yml", Config::YAML);
	}
	
	public function saveCache(){
		return $this->cache()->save();
	}
	
	public function set(string $k, string|array $v) :mixed{ 
		return $this->cache()->set($k, $v);
	}
	
	public function get(string $k) :mixed{ 
		return $this->cache()->get($k);
	}
}