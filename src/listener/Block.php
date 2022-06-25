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

namespace xqwtxon\HiveProfanityFilter\listener;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use pocketmine\event\Event;

class Block implements Listener {
	private LanguageManager $lang;
	private ConfigManager $config;
	public function __construct(Loader $plugin){
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
	}
	public function onChat(PlayerChatEvent $ev) :void {
		$msg = $ev->getMessage();
		$player = $ev->getPlayer();
		if ($this->plugin->containsProfanity($msg)){
			$ev->cancel();
		}
	}
	
	
}