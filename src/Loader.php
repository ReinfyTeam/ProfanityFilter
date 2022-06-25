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

namespace xqwtxon\HiveProfanityFilter;

use pocketmine\plugin\PluginBase;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\CacheManager;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\KickManager;
use xqwtxon\HiveProfanityFilter\utils\FormManager;
use xqwtxon\HiveProfanityFilter\listener\Block;
use xqwtxon\HiveProfanityFilter\listener\BlockWithMessage;
use xqwtxon\HiveProfanityFilter\command\ProfanityCommand;
use xqwtxon\HiveProfanityFilter\Updater;
use xqwtxon\HiveProfanityFilter\Watchdog;

class Loader extends PluginBase {
	private static Loader $instance;
	public function onLoad() :void{
		Loader::$instance = $this;
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->lang->saveAllLang();
		$this->config->saveProfanity();
		$this->config->checkConfig();
		$this->lang->checkCustomLang();
	}
	public function onEnable() :void{
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->cache = new CacheManager();
		if(!is_dir($this->getDataFolder() . "cache/")){
			@mkdir($this->getDataFolder() . "cache/");
		}
		$this->saveResource("cache/violations.yml");
		$this->cache->saveCache();
		$this->getServer()->getPluginManager()->registerEvents(new Watchdog(), $this);
		$this->loadListeners();
		$this->loadCommands();
		$this->checkUpdate();
	}
	private function loadCommands(){
		$this->getServer()->getCommandMap()->register("HiveProfanityFilter", new ProfanityCommand());
	}
	private function loadListeners(){
		switch(strtolower($this->getConfig()->get("type"))){
			case "block-with-message":
				$this->getServer()->getPluginManager()->registerEvents(new BlockWithMessage($this), $this);
				break;
			case "block":
				$this->getServer()->getPluginManager()->registerEvents(new Block($this), $this);
				break;
			default:
				$this->getLogger()->debug("Unable to get option 'type' in config.yml.");
				$this->getLogger()->notice("Unable to get option 'type' in config.yml. Make sure the 'type' option is correct. As default we modify value of 'type' to 'block-with-message'");
				$this->getConfig()->set("type", "block-with-message");
				$this->getConfig()->saveConfig();
				break;
		}
	}
	
	public static function getInstance(): Loader {
		return Loader::$instance;
	}
	public function getConfigVersion() {
		return "0.0.3";
	}
	
	private function checkUpdate(bool $isEntry = false) :void{
		$this->getServer()->getAsyncPool()->submitTask(new Updater($this->getDescription()->getName(), $this->getDescription()->getVersion()));
	}
	
	protected function onDisable() :void {
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->lang->saveAllLang();
		$this->config->saveProfanity();
	}
	
	public function containsProfanity(string $msg): bool {
		$profanities = (array) $this->config->profanityGet("banned-words");
		$filterCount = sizeof($profanities);
		for ($i = 0; $i < $filterCount; $i++) {
			$condition = preg_match('/' . $profanities[$i] . '/iu', $msg) > 0;
			if ($condition) {
				return true;
			}
		}
		return false;
	}
}