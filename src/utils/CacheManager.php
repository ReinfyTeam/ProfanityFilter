<?php

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