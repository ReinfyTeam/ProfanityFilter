<?php

namespace xqwtxon\HiveProfanityFilter\type;

use xqwtxon\HiveProfanityFilter\Main;
use xqwtxon\HiveProfanityFilter\utils\language;
use xqwtxon\HiveProfanityFilter\utils\config_manager;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Event;

class block implements Listener {
	private language $lang;
	private config_manager $config;
	public function __construct(Main $plugin){
		$this->lang = $lang;
		$this->config = $config;
	}
	public function onChat(PlayerChatEvent $ev) :void {
		$msg = $ev->getMessage();
		$player = $ev->getPlayer();
		$words = array_map("strtolower", $this->config->banned_words()->get("banned-words"), []));
		if (in_array($words), $msg){
			$ev->cancel();
		}
	}
	
	
}