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
use function mkdir;

class LanguageManager {
	
	public static string $defaultLang;
	/** @var string[] $players */
	public static array $players = [];
	/** @var string[][] $languages */
	public static array $languages = [];
	
	public function __construct(){
		$defaultLang = "en-us";
	}
	private array $langs = [
		'en-us',
	];
	public function translateMessage(array|string $k) :array|string{
		$this->config = new ConfigManager();
		$lang = $this->getSelectedLang();
		if(!file_exists(Loader::getInstance()->getDataFolder() . "languages/" . $lang . ".yml")){
			$this->saveAllLang();
		}
		if(Loader::getInstance()->getConfig()->get("lang") === null){
			$instance = new Config(Loader::getInstance()->getDataFolder() . "languages/" . $defaultLang . ".yml", Config::YAML);
			return $instance->get($k);
		}
			$instance = new Config(Loader::getInstance()->getDataFolder() . "languages/" . $lang . ".yml", Config::YAML);
			return $instance->get($k);
	}
	public function getSelectedLang(){
		if(Loader::getInstance()->getConfig()->get("lang") === null) {
			throw new Exception("Failed to get selected languages in config.yml! Possible blank option provided. Please delete config.yml to fix this problem. Unable to find option: lang in config.yml returned: null");
		}
		if(Loader::getInstance()->getConfig()->get("lang") === null){
			return $defaultLang;
		}
		if(Loader::getInstance()->getConfig()->get("lang") === "custom"){
			//@delete($this->config->getDataFolder() . "languages/" . "en-us.yml");
			return "custom";
		}
		return Loader::getInstance()->getConfig()->get("lang");
	}
	public function saveAllLang(){
		if(!is_dir(ConfigManager::getDataFolder() . "languages")) {
			Loader::getInstance()->saveResource(ConfigManager::getDataFolder() . "languages/");
		}
		foreach($this->langs as $languages){
			Loader::getInstance()->saveResource("languages/" . $languages . ".yml");
		}
	}
	
	public function checkCustomLang(){
		if(strtolower($this->getSelectedLang()) === "custom"){
			return Loader::getInstance()->saveResource("languages/custom.yml");
		} else {
			return true;
		}
		return false;
	}
}