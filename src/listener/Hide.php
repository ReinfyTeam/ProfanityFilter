<?php

namespace xqwtxon\HiveProfanityFilter\listener;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Event;
use pocketmine\event\Listener;

class Hide implements Listener {
	private LanguageManager $lang;
	private ConfigManager $config;
	public function __construct(Loader $plugin){
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
	}
	public function onChat(PlayerChatEvent $ev) :void {
		$chat_format = $ev->getFormat();
		$msg = $ev->getMessage();
		$player = $ev->getPlayer();
		$playerName = $player->getName();
		$words = array_map("strtolower", $this->config->profanityGet("banned-words"), []);
		if (in_array($words) === $msg){
			$ev->cancel();
			// HIVE TRICK FOR HIDING MESSAGE ;)
			// IT WILL DONT BROADCAST TO OTHER PLAYERS INSTEAD FROM YOU.
			$player->sendMessage($chat_format . " " . $message);
		}
	}
	
	
}