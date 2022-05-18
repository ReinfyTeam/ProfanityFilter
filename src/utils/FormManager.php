<?php

namespace xqwtxon\HiveProfanityFilter\utils;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use pocketmine\player\Player;
use xqwtxon\HiveProfanityFilter\libs\jojoe77777\FormAPI\SimpleForm;
use xqwtxon\HiveProfanityFilter\libs\jojoe77777\FormAPI\CustomForm;
use xqwtxon\HiveProfanityFilter\libs\jojoe77777\FormAPI\ModalForm;

class FormManager {
	public function __construct(){
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
		$this->plugin = Loader::getInstance();
	}
	
	public function manageProfanity(Player $player){
		
	}
}