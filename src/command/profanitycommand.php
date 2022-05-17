<?php

namespace xqwtxon\HiveProfanityFilter\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use xqwtxon\HiveProfanityFilter\Main;
use xqwtxon\HiveProfanityFilter\utils\language;
use xqwtxon\HiveProfanityFilter\utils\config_manager;
use pocketmine\player\Player;

class profanity_command extends Command {
	// Constructors
	private Main $plugin;
	private language $lang;
	private config_manager $config;
	private gui_manager $ui
	
	private array $authors = [
		"xqwtxon",
		"z3nXxz"
	];
	
	public function __construct(){
		$this->lang = $lang;
		$this->config = $config;
		$this->plugin = $plugin;
		$this->ui = $ui;
		$this->setPermission($this->plugin->getConfig()->get("profanity-command-perm"));
		$this->usageMessage("/pf <help/sub_command>");
		$this->setDescription("profanity-command-description");
	}
	
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		
		$translate = $this->lang;
		$currentLang = $cfg->get("lang");
		if(!$sender instanceof Player) return;
		
		if(!isset($args[0]){
			$this->usageMessage();
		}
		switch($args[0]){
			case "help":
				$sender->sendMessage($translate->getKey($currentLang,"help-title"));
				$sender->sendMessage($translate->getKey($currentLang,"help-subtitle"));
				foreach($translate->getKey($currentLang,"help-page-1") as $page){
					$sender->sendMessage($page);
				}
				break;
			case "help 1":
				$sender->sendMessage($translate->getKey($currentLang,"help-title"));
				$sender->sendMessage($translate->getKey($currentLang,"help-subtitle"));
				foreach(array_chunk($translate->getKey($currentLang,"help-page-1"), 5) as $page){
					$sender->sendMessage($page);
				}
				break;
			case "help 2":
				$sender->sendMessage($translate->getKey($currentLang,"help-title"));
				$sender->sendMessage($translate->getKey($currentLang,"help-subtitle"));
				foreach(array_chunk($translate->getKey($currentLang,"help-page-2"), 3) as $page){
					$sender->sendMessage($page);
				}
				break;
			case "ui":
				$this->ui->manage_profanity($sender);
				break;
			case "credits":
				$this->sendMessage($translate->getKey($currentLang,"credits-title"));
				$this->sendMessage($translate->getKey($currentLang,"credits-subtitle"));
				$this->sendMessage($translate->getKey($currentLang,"credits-description"));
				foreach($authors){
					$this->sendMessage("- " . $authors);
				}
				break;
			case "stop":
				$this->sendMessage($translate->getKey($currentLang,"stopped-plugin"));
				$this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
				break;
			case "add":
				if(!isset($args[1]) $this->sendMessage($translate->getKey($currentLang,"add-subcommand-usage"));
				$this->config->banned_words->set($args[1]);
				$this->sendMessage($translate->getKey($currentLang,"banned_words_added"));
				break;
			case "remove":
				if(!isset($args[1]) $this->sendMessage($translate->getKey($currentLang,"remove-subcommand-usage"));
				$this->config->banned_words->set("banned-words",$args[1]);
				$this->sendMessage($translate->getKey($currentLang,"banned_words_remove"));
				break;
			case "check-update":
				break;
			case "type":
				if(!isset($args[1]) $this->sendMessage($translate->getKey($currentLang,"type-subcommand-usage"));
				switch(strtolower($args[1])){
					case "hide":
						$this->plugin->getConfig()->set("type",strtolower($args[1]));
						break;
					case "block":
						$this->plugin->getConfig()->set("type",strtolower($args[1]));
						break;
					case "block-with-message":
						$this->plugin->getConfig()->set("type",strtolower($args[1]));
						break;
					default:
						$this->sendMessage($translate->getKey($currentLang,"type-subcommand-usage"));
						break;
				}
				$this->sendMessage($translate->getKey($currentLang,"banned_words_remove"));
				break;
		}
	}
}