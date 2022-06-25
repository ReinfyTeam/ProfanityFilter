<?php

namespace xqwtxon\HiveProfanityFilter\command;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\Loader;
use pocketmine\player\Player;
use xqwtxon\HiveProfanityFilter\utils\FormManager;
use pocketmine\utils\TextFormat;
use xqwtxon\HiveProfanityFilter\Updater;

class ProfanityCommand extends Command {
	private ConfigManager $config;
	private LanguageManager $lang;
	private Loader $plugin;
	public function __construct(){
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
		$this->ui = new FormManager();
		$this->updater = new Updater();
		$this->plugin = Loader::getInstance();
		$this->setPermission("profanity.command");
		parent::__construct("pf", "HiveProfanityFilter Command", $this->lang->translateMessage("profanity-command-usage"), ["pf"]);
	}
	private array $pluginCreators = [
		'xqwtxon',
		'z3nXxz',
	];
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			$sender->sendMessage(TextFormat::RED . $this->lang->translateMessage("profanity-command-usage-execute"));
			return;
		}
		
		switch(strtolower($args[0])){
			case "help":
				$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("help-title"));
				$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("help-subtitle"));
				foreach($this->lang->translateMessage("help-page") as $commands){
					$sender->sendMessage("- " . TextFormat::GREEN . $commands);
				}
				break;
			case "ui":
					if(!$sender instanceof Player){
						$sender->sendMessage(TextFormat::RED . $this->lang->translateMessage("profanity-command-only-ingame"));
						return;
					}
					$this->ui->manageProfanity($sender);
				break;
			case "banned-words":
				$sender->sendMessage(TextFormat::GOLD . $this->lang->translateMessage("banned-words-description"));
				foreach($this->config->profanityGet("banned-words") as $word){
					$sender->sendMessage("- " . TextFormat::RED .  $word);
				}
				$sender->sendMessage(TextFormat::GREEN . $this->lang->translateMessage("banned-words-description-2"));
				break;
			case "list":
				$sender->sendMessage(TextFormat::GOLD . $this->lang->translateMessage("banned-words-description"));
				foreach($this->config->profanityGet("banned-words") as $word){
					$sender->sendMessage("- " . TextFormat::RED .  $word);
				}
				$sender->sendMessage(TextFormat::GREEN . $this->lang->translateMessage("banned-words-description-2"));
				break;
			case "credits":
				$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("credits-title"));
				$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("credits-subtitle"));
				$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("credits-description"));
				foreach($this->pluginCreators as $author){
					$sender->sendMessage("- ". TextFormat::GREEN . $author);
				}
				break;
			case "info":
				$sender->sendMessage(TextFormat::GREEN . "This is " . $this->plugin->getFullName() . " by xqwtxon. Config Version: " . $this->plugin->getConfigVersion());
				break;
			case "type":
				if(!isset($args[1])){
					$sender->sendMessage(TextFormat::RED . $this->lang->translateMessage("profanity-command-type-usage"));
					return;
				}
				switch(strtolower($args[1])){
					case "block":
						$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("profanity-command-type-success-tip"));
						$sender->sendMessage(TextFormat::GREEN . $this->lang->translateMessage("profanity-command-type-success") . " block");
						$this->plugin->getConfig()->set("type", "block");
						$this->plugin->saveConfig();
						$this->plugin->getConfig()->reload();
						break;
					case "block-with-message":
						$sender->sendMessage(TextFormat::YELLOW . $this->lang->translateMessage("profanity-command-type-success-tip"));
						$sender->sendMessage(TextFormat::GREEN . $this->lang->translateMessage("profanity-command-type-success") . " block-with-message");
						$this->plugin->getConfig()->set("type", "block-with-message");
						$this->plugin->saveConfig();
						$this->plugin->getConfig()->reload();
						break;
					case "current":
						$sender->sendMessage(TextFormat::GREEN . $this->lang->translateMessage("profanity-command-type-current") . " " . $this->plugin->getConfig()->get("type"));
						break;
					default:
						$sender->sendMessage(TextFormat::RED . $this->lang->translateMessage("profanity-command-usage-execute"));
						break;
				}
				break;
			case "update":
				$this->updater->CommandUpdate($sender);
				break;
			default:
				$sender->sendMessage(TextFormat::RED . $this->lang->translateMessage("profanity-command-usage-execute"));
				break;
		}
	}
}