<?php

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
		'ph-fl',
	];
	public function translateMessage(array|string $k) :array|string{
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
}