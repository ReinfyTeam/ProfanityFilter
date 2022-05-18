<?php

namespace xqwtxon\HiveProfanityFilter\listener;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Event;

class Hide implements Listener {
	private language $lang;
	private config_manager $config;
	public function __construct(Loader $plugin){
		$this->lang = $lang;
		$this->config = $config;
	}
	public function onChat(PlayerChatEvent $ev) :void {
		$chat_format = $ev->getFormat();
		$msg = $ev->getMessage();
		$player = $ev->getPlayer();
		$playerName = $player->getName();
		$words = array_map("strtolower", $this->config->profanity_get("banned-words"), []));
		if (in_array($words), $msg){
			$ev->cancel();
			// HIVE TRICK FOR HIDING MESSAGE ;)
			// IT WILL DONT BROADCAST TO OTHER PLAYERS INSTEAD FROM YOU.
			$player->sendMessage($chat_format . " " . $message);
		}
	}
	
	
}