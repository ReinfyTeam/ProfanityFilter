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

use pocketmine\player\Player;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\Loader;

class KickManager {
	//TODO: Kick players when reached amount of violation.
	
	private LanguageManager $lang;
	private ConfigManager $config;
	
	public function __construct(){
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
		$this->plugin = Loader::getInstance();
	}
	
	public function kick(Player $p, string|array $r) :mixed{
		$p->kick($r, true);
	}
	
	public function disconnect(Player $p, string|array $r) :mixed{
		$p->kick($r, true);
		$this->addViolation($p,1);
	}
	
	public function addViolation(Player $p, int $n) :int{
		$c = $this->cache->playersCache()->get($p->getName());
		$s = $c + $n;
		$this->cache->playersCache()->set($p->getName(), $s);
	}
}