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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\CacheManager;
use xqwtxon\HiveProfanityFilter\utils\KickManager;
use xqwtxon\HiveProfanityFilter\Loader;

class Watchdog implements Listener {
	private KickManager $kicker;
	private ConfigManager $config;
	private CacheManager $cache;
	private LanguageManager $lang;
	public function __construct(){
		$this->plugin = Loader::getInstance();
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
		$this->cache = new CacheManager();
		$this->kicker = new KickManager();
	}
	
	public function onChat(PlayerChatEvent $ev) :void{
		$player = $ev->getPlayer();
		$violation = $this->cache->get($player->getName());
		$maxViolations = $this->plugin->getConfig()->get("max-violation");
		$type = $this->plugin->getConfig()->get("punishment-type");
		$message = $this->plugin->getConfig()->get("kick-message");
		if($type === "kick"){
			if($violations > $maxViolations){
				$this->kicker->kick($p, $message);
				$this->cache->set($player->getName(), 0);
				$this->cache->saveCache();
			}
		}
		if($type === "ban"){
			if($violations > $maxViolations){
				//TODO: ban players when reached max violations.
			}
		}
	}
	
	public function onJoin(PlayerJoinEvent $ev) :void{
		//RESET THEIR MAX VIOLATIONS IN CONFIG
		//PREVENTS TO BE KICKED.
		$this->cache->set($ev->getPlayer()->getName(), 0);
	}
	
	
}