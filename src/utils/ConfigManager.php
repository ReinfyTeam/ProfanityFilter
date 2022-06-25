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
use Exception;
use LogicException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;

class ConfigManager {
	
	public function __construct(){
		$this->lang = new LanguageManager();
	}
	
	public function checkConfig(){
		if (!file_exists(Loader::getInstance()->getConfig()->getPath())){
			Loader::getInstance()->getLogger()->debug($this->lang->translateMessage("config-notfound"));
			Loader::getInstance()->saveResource("config.yml");
			Loader::getInstance()->saveConfig();
			Loader::getInstance()->getConfig()->reload();
		}
		if (!is_dir(Loader::getInstance()->getDataFolder())){
			Loader::getInstance()->getLogger()->debug($this->lang->translateMessage("plugin-dir-notfound"));
		}
		if (!is_file(Loader::getInstance()->getDataFolder() . "/config.yml")){
			Loader::getInstance()->getLogger()->debug($this->lang->translateMessage("config-corrupted"));
		}
		if (!Loader::getInstance()->getConfig()->get("config-version") == Loader::getInstance()->getConfigVersion()){
			Loader::getInstance()->getLogger()->info($this->lang->translateMessage("outdated-config"));
			@rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "old-config.yml");
			Loader::getInstance()->saveResource("config.yml");
		}
		if(!file_exists($this->getDataFolder() . "banned-words.yml")){
			$this->saveProfanity();
		}
	}
	
	public function profanityGet(string|array $k) :string|array {
		return  $this->profanity()->get($k);
	}
	public function profanitySet(string|array $k, string|array $v) :mixed {
		return $this->profanity()->set($k, $v);
	}
	
	public static function getDataFolder() {
		return Loader::getInstance()->getDataFolder();
	}
	
	public function profanity(){
		return new Config($this->getDataFolder() . "/banned-words.yml", Config::YAML);
	}
	
	public function saveProfanity(){
		return Loader::getInstance()->saveResource("banned-words.yml");
		//return $this->profanity()->save();
	}
	
	public function reloadProfanity(){
		return $this->profanity()->reload();
	}
}