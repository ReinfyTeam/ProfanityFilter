<?php

declare(strict_types=1);

namespace xqwtxon\HiveProfanityFilter;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use xqwtxon\HiveProfanityFilter\utils\config_manager;
use function rename;

class Main extends PluginBase{
	/** @phpstan-param Config_Manager $config **/
	private config_manager $config;
	/** @phpstan-param Language $lang **/
	private language $lang;
	
	public function onLoad() :void {
		config_manager::check_config();
		//$this->updater->update();
	}
    protected function onEnable() :void {
		$this->lang = $lang;
		$this->config = $config;
		
		// SAVE ALL THE LANGS
		foreach($this->lang->getAllLangs() as $path){
		    $this->saveResource($this->getDataFolder() . "/lang/" . $path . ".yml");
		}
		
		// SAVES ALL THE CONFIG
		config_manager::saveConfig();
	}
	
	protected function onDisable(){
		// SAVE AGAIN THE CONFIG AFTER DISABLED
		config_manager::saveConfig();
	}
}
