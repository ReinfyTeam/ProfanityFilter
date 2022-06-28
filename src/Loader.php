<?php

/*  					
 *					   _
 * 					  | |                  
 * __  ____ ___      _| |___  _____  _ __  
 * \ \/ / _` \ \ /\ / / __\ \/ / _ \| '_ \ 
 *  >  < (_| |\ V  V /| |_ >  < (_) | | | |
 * /_/\_\__, | \_/\_/  \__/_/\_\___/|_| |_|
 *         | |                             
 *         |_|                             
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author xqwtxon
 * @link https://github.com/xqwtxon/
 *
*/

namespace ProfanityFilter;

use pocketmine\plugin\PluginBase;
use ProfanityFilter\DefaultCommand;
use ProfanityFilter\EventListener;

class Loader extends PluginBase {
    
    /** @var Loader $instance **/
    private Loader $instance;
    
    /** @var int $punishment **/
    private int $punishment = 0;
    
    
    public function onLoad() :void {
        Loader::$instance = $this;
        $this->checkConfig();
        $this->checkUpdate();
    }
    
    public function onEnable() :void {
        $this->registerCommands();
        $this->loadListeners();
    }
    
    public static function getInstance() : Loader {
        return Loader::$instance;
    }
    
    private function checkConfig() :void {
        $log = $this->getLogger();
	    $pluginConfigResource = $this->getResource("config.yml");
	    $pluginConfig = yaml_parse(stream_get_contents($pluginConfigResource));
	    fclose($pluginConfigResource);
	    $config = $this->getConfig();
		
	    if($pluginConfig == false) {
	    	$log->critical("Invalid Configuration Syntax, Please remove your config.yml to fix.");
	    	$this->getServer()->getPluginManager()->disablePlugin($this);
	    	return;
	    }
        if($config->get("config-version") === $pluginConfig["config-version"]) return;
	    $log->notice("Your configuration is outdated!");
	    $log->info("Your old config.yml is renamed as old-config.yml");
	    @rename($this->getDataFolder(). 'config.yml', 'old-config.yml');
	    @unlink($this->getDataFolder() . "old-config.yml");
	    $this->saveResource("config.yml");
    }
    
    private function loadListener() : void {
        switch($this->getConfig()->get("type")){
            case "block":
                $this->getServer()->getPluginManager()->registerEvents(new EventListener("block"), $this);
                break;
            case "hide":
                $this->getServer()->getPluginManager()->registerEvents(new EventListener("hide"), $this);
                break;
            default:
                $this->getLogger()->critical("Invalid Profanity Type. Please check instruction on your configuration.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                break;
        }
    }
    
    private function registerCommands() : void {
        $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), new DefaultCommand());
    }
    
    /*
     * Format Message. Dont call it directly.
     *
     * @param string $message
     * @return string
    */
    protected function formatMessage(string $message) : string {
        $message = str_replace("{type}", $this->getConfig()->get("type"), $message);
        return $message;
    } 
}