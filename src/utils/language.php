<?php

namespace xqwtxon\HiveProfanityFilter\utils;

use pocketmine\utils\Config;

class language {
	
	protected array $languages = [
	// You can make your own languages by pull requesting on github.
		"en-us",
		"fl-ph",
	];
    public function getLang(string $lang){
		if(!isset($lang)) return;
		
		if(in_array($languages) === $lang){
			return new Config($this->getDataFolder() . "/lang/" . $lang . ".yml", Config::YAML);
		}
	}		
	public function saveLang(string $path){
		$file = $this->getLang($path);
		$file->save();
	}
	public function saveAllLangs() {
		foreach($languages as $path){
			$file = $this->getLang($path);
			$file->save();
		}
	}
	public function getKey(string $lang, string $k){
		if(!isset($k)) return;
		if(!isset($lang)) return;
		return $this->getLang($lang)->get($k);
	}
	
	public function setKey(string $lang, string $k){
		if(!isset($k)) return;
		return $this->getLang($lang)->set($k);
	}
	
	public function getAllLangs(){
		return $languages[];
	}
	// TODO:
	/*public function repairLang(){
		$this->getConfig()->save();
		Main::getInstance()->saveResource("banned-words.yml");
	}*/
}