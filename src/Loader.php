<?php
declare(strict_types=1);

namespace xqwtxon\HiveProfanityFilter;

use pocketmine\plugin\PluginBase;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use xqwtxon\HiveProfanityFilter\listener\Hide;
use xqwtxon\HiveProfanityFilter\listener\Block;
use xqwtxon\HiveProfanityFilter\listener\BlockWithMessage;
use xqwtxon\HiveProfanityFilter\command\ProfanityCommand;
use xqwtxon\HiveProfanityFilter\Updater;

class Loader extends PluginBase {
	private static Loader $instance;
	public function onLoad() :void{
		Loader::$instance = $this;
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->lang->saveAllLang();
		$this->config->saveProfanity();
		$this->config->checkConfig();
		$this->lang->checkCustomLang();
	}
	public function onEnable() :void{
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->updater = new Updater();
		$this->loadListeners();
		$this->loadCommands();
		$this->updater->Update();
	}
	private function loadCommands(){
		$this->getServer()->getCommandMap()->register("HiveProfanityFilter", new ProfanityCommand());
	}
	private function loadListeners(){
		switch(strtolower($this->getConfig()->get("type"))){
			case "hide":
				$this->getServer()->getPluginManager()->registerEvents(new Hide($this), $this);
				break;
			case "block-with-message":
				$this->getServer()->getPluginManager()->registerEvents(new BlockWithMessage($this), $this);
				break;
			case "block":
				$this->getServer()->getPluginManager()->registerEvents(new Block($this), $this);
				break;
			default:
				$this->getLogger()->debug("Unable to get option 'type' in config.yml.");
				$this->getLogger()->notice("Unable to get option 'type' in config.yml. Make sure the 'type' option is correct. As default we modify value of 'type' to 'block-with-message'");
				$this->getConfig()->set("type", "block-with-message");
				break;
		}
	}
	
	public static function getInstance(): Loader {
		return Loader::$instance;
	}
	public function getConfigVersion() {
		return "0.0.1";
	}
	protected function onDisable() :void {
		$this->config = new ConfigManager();
		$this->lang = new LanguageManager();
		$this->lang->saveAllLang();
		$this->config->saveProfanity();
	}
}