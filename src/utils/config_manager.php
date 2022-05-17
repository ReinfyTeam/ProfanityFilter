<?php

namespace xqwtxon\HiveProfanityFilter\utils;

use pocketmine\utils\Config;
use xqwtxon\HiveProfanityFilter\utils\language;
use xqwtxon\HiveProfanityFilter\Main;

class config {
	private const CONFIG_VERSION = "1.0.0";
	private language $lang;
	private Main $plugin;
	public function __construct(){
		$this->lang = $lang;
		$this->plugin = $plugin;
	}
	public static function banned_words(){
        return new Config($this->plugin->getDataFolder() . "/banned-words.yml", Config::YAML);
    }
	public static function save_config(){
		$this->plugin->saveDefaultConfig();
		$this->banned_words()->save();
		$this->lang->getConfig()->save();
	}
	public function check_config(){
		$version = $this->plugin->getConfig()->get("config-version");
		$log = $this->plugin->getLogger();
		if (!$version === $this::CONFIG_VERSION){
			@rename($this->plugin->getDataFolder() . "/config.yml", $this->plugin->getDataFolder() . "/old-config.yml");
			$log->info("Your configuration is outdated. The configuration was renamed as old-config.yml");
			return;
		}
	}
}