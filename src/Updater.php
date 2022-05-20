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
			$updateURL = "https://raw.githubusercontent.com/xqwtxon/HiveProfanityFilter/main/version.json";
			$json = @file_get_contents($updateURL);
			if (($data = $json) === false) {
				return $this->plugin->getLogger()->critical("Unable to Check Update. Check your connection and try again.");
			} else {
				$obj = json_decode($json);
				$version = $obj->{'version'}; 
				$details = $obj->{'details'};
				$download = $obj->{'download'};
				$date = $obj->{'date-release'};
			}
			$ver = "0.0.1-BETA";
			if($version === $ver){
				$this->plugin->getServer()->getLogger()->notice("[Update Checker] ". $this->lang->translateMessage("no-updates-found"));
			} else {
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] ". $this->lang->translateMessage("new-update-found") . $this->lang->translateMessage("new-update-ver-text") . $version . $this->lang->translateMessage("new-update-ver-released-date") . $date);
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] Details: ". $details);
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] Download: ". $download);
			}
	}
}