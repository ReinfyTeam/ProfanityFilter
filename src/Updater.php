<?php

namespace xqwtxon\HiveProfanityFilter;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use pocketmine\utils\TextFormat;

class Updater {
	public function __construct(){
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->plugin = Loader::getInstance();
	}
	public function Update(){
			$json = @file_get_contents($this->getDefaultUpdater());
			if (($data = $json) === false) {
				return $this->plugin->getServer()->getLogger()->critical("[Update Checker] ". $this->lang->translateMessage("update-error"));
				$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check failed due to 'Could not resolve host: {$this->getDefaultUpdater}'");
			} else {
				$obj = json_decode($json);
				try {
					$version = $obj->{'version'}; 
					$details = $obj->{'details'};
					$download = $obj->{'download'};
					$date = $obj->{'date-release'};
				}
				catch(Exception $exception){
					$this->plugin->getServer()->getLogger()->notice("[Update Checker] ". $this->lang->translateMessage("update-error"));
					$this->plugin->getServer()->getLogger()->debug("[Update Checker] ". $this->lang->translateMessage("update-checking-mirrors"));
					$this->otherMirror("https://raw.githubusercontent.com/xqwtxon/HiveProfanityFilter/update-mirror-1/version.json");
				}
			}
			if($version === $this->plugin->getPluginVersion()){
				$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update check found: No Update Found.");
				$this->plugin->getServer()->getLogger()->notice("[Update Checker] ". $this->lang->translateMessage("no-updates-found"));
			} else {
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] ". $this->lang->translateMessage("new-update-found") . $this->lang->translateMessage("new-update-ver-text") . $version . $this->lang->translateMessage("new-update-ver-released-date") . $date);
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] Details: ". $details);
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] Download: ". $download);
			}
	}
	public function CommandUpdate($sender){
			$json = @file_get_contents($this->getDefaultUpdater());
			if (($data = $json) === false) {
				return $sender->sendMessage(TextFormat::RED . "[Update Checker] ". $this->lang->translateMessage("update-error"));
				$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check failed due to 'Could not resolve host: {$this->getDefaultUpdater}'");
			} else {
				$obj = json_decode($json);
				try {
					$version = $obj->{'version'}; 
					$details = $obj->{'details'};
					$download = $obj->{'download'};
					$date = $obj->{'date-release'};
				}
				catch(Exception $exception){
					$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check failed due to 'invalid provided json.'");
					$sender->sendMessage(TextFormat::RED . "[Update Checker] ". $this->lang->translateMessage("update-error"));
					$sender->sendMessage(TextFormat::DARK_RED . "[Update Checker] ". $this->lang->translateMessage("update-checking-mirrors"));
					$this->otherCommandMirror("https://raw.githubusercontent.com/xqwtxon/HiveProfanityFilter/update-mirror-1/version.json");
				}
			}
			if($version === $this->plugin->getPluginVersion()){
				$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Checker found: No updates found.");
				$sender->sendMessage(TextFormat::CYAN . "[Update Checker] ". $this->lang->translateMessage("no-updates-found"));
			} else {
				$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check found new update.");
				$sender->sendMessage(TextFormat::YELLOW . "[Update Checker] ". $this->lang->translateMessage("new-update-found") . $this->lang->translateMessage("new-update-ver-text") . $version . $this->lang->translateMessage("new-update-ver-released-date") . $date);
				$sender->sendMessage(TextFormat::YELLOW . "[Update Checker] Details: ". $details);
				$sender->sendMessage(TextFormat::YELLOW . "[Update Checker] Download: ". $download);
			}
	}
	public function getDefaultUpdater(){
		return "https://raw.githubusercontent.com/xqwtxon/HiveProfanityFilter/main/version.json";
	}
	public function otherMirror(string $url) :mixed{
		$json = @file_get_contents($url);
		if(($data = $json) === false){
			$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check failed due to 'Could not resolve host: {$url}'");
			return $this->plugin->getLogger()->critical("[Update Checker] ". $this->lang->translateMessage("update-error"));
		}
			$obj = json_decode($json);
				try {
					$version = $obj->{'version'}; 
					$details = $obj->{'details'};
					$download = $obj->{'download'};
					$date = $obj->{'date-release'};
				}
				catch(Exception $exception){
					$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check failed due to 'invalid provided json.'");
					$this->plugin->getServer()->getLogger()->notice("[Update Checker] ". $this->lang->translateMessage("update-error"));
					$this->plugin->getServer()->getLogger()->debug("[Update Checker] ". $this->lang->translateMessage("update-error-checking-mirrors"));
				}
		if($version === $this->plugin->getPluginVersion()){
			$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Checker found: No updates found.");
			$this->plugin->getServer()->getLogger()->notice("[Update Checker] ". $this->lang->translateMessage("no-updates-found"));
		} else {
				$this->plugin->getServer()->getLogger()->debug("[Update Checker] Update Check found new update.");
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] ". $this->lang->translateMessage("new-update-found") . $this->lang->translateMessage("new-update-ver-text") . $version . $this->lang->translateMessage("new-update-ver-released-date") . $date);
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] Details: ". $details);
				$this->plugin->getServer()->getLogger()->warning("[Update Checker] Download: ". $download);
		}
	}
	public function otherCommandMirror($sender, string $url) :mixed{
		$json = @file_get_contents($url);
		if(($data = $json) === false){
			return $sender->sendMessage("[Update Checker] Unable to Check Update. Check your connection and try again.");
		}
			$obj = json_decode($json);
				try {
					$version = $obj->{'version'}; 
					$details = $obj->{'details'};
					$download = $obj->{'download'};
					$date = $obj->{'date-release'};
				}
				catch(Exception $exception){
					$sender->sendMessage("[Update Checker] ". $this->lang->translateMessage("update-error"));
					$sender->sendMessage("[Update Checker] ". $this->lang->translateMessage("update-error-checking-mirrors"));
				}
		if($version === $this->plugin->getPluginVersion()){
			$sender->sendMessage("[Update Checker] ". $this->lang->translateMessage("no-updates-found"));
		} else {
				$sender->sendMessage(TextFormat::YELLOW . "[Update Checker] ". $this->lang->translateMessage("new-update-found") . $this->lang->translateMessage("new-update-ver-text") . $version . $this->lang->translateMessage("new-update-ver-released-date") . $date);
				$sender->sendMessage(TextFormat::YELLOW . "[Update Checker] Details: ". $details);
				$sender->sendMessage(TextFormat::YELLOW . "[Update Checker] Download: ". $download);
		}
	}
}