<?php

namespace xqwtxon\HiveProfanityFilter;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;

class Updater {
	public function __construct(){
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->plugin = Loader::getInstance();
	}
	public function Update(){
		try{
			$updateURL = "https://raw.githubusercontent.com/xqwtxon/HiveProfanityFilter/raw/blob/main/version.json";
			$json = file_get_contents($updateURL);
			$this->saveJson();
			$obj = json_decode($json);
			$version = $obj->{'version'}; 
			$details = $obj->{'details'};
			$download = $obj->{'download'};
			if($version === "0.0.1-BETA"){
				return $this->plugin->getServer()->getLogger()->info("[Updater] ". $this->lang->translateMessage("new-update-found"));
			} else {
				return $this->plugin->getServer()->getLogger()->info("[Updater] ". $this->lang->translateMessage("no-updates-found"));
			}
		}
		catch(Exception $exeption){
			return $this->plugin->getLogger()->error("Unable to Check Update. Check your connection and try again.");
		}
	}
	
	public function saveJson(){
		return file_put_contents($this->config->getDataFolder() . "cache/version.json", $json);
	}
}