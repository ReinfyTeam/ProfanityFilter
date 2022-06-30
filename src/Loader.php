<?php

/*  					
 *			        _
 * 				  | |                  
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

declare(strict_types=1);

namespace ProfanityFilter;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;
use ProfanityFilter\Command\DefaultCommand;
use ProfanityFilter\EventListener;
use ProfanityFilter\Utils\Language;
use ProfanityFilter\Tasks\UpdateTask;
use function yaml_parse;

class Loader extends PluginBase {
    
    /** @var Loader $instance **/
    private static Loader $instance;
    
    /** @var int $punishment **/
    public int $punishment = 0;
    
    
    public function onLoad() :void {
        Loader::$instance = $this;
        $this->checkConfig();
        $this->checkUpdate();
        $this->saveResources();
    }
    
    public function onEnable() :void {
        $this->registerCommands();
        $this->loadListeners();
        (new Language())->init();
    }
    
    public static function getInstance() : Loader {
        return Loader::$instance;
    }
    
    private function checkConfig() :void {
         $log = $this->getLogger();
	    $pluginConfigResource = $this->getResource("config.yml");
	    $lang = new Language();
	    $pluginConfig = yaml_parse(stream_get_contents($pluginConfigResource));
	    fclose($pluginConfigResource);
	    $config = $this->getConfig();
		
	    if($pluginConfig == false) {
	    	$log->critical("Invalid Configuration Syntax, Please remove your config.yml to fix.");
	    	$this->getServer()->getPluginManager()->disablePlugin($this);
	    	return;
	    }
        if($config->get("config-version") === $pluginConfig["config-version"]) return;
	    $log->notice($lang->translateMessage("outdated-config"));
	    @rename($this->getDataFolder(). 'config.yml', 'old-config.yml');
	    @unlink($this->getDataFolder() . "old-config.yml");
	    $this->saveResource("config.yml");
    }
    
    private function loadListeners() : void {
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
    public function formatMessage(string $message) : string { // TODO: Move this in the event class
         $message = str_replace("{type}", $this->getConfig()->get("type"), $message);
        return $message;
    } 
    
    private function checkUpdate() :void {
         $this->getServer()->getAsyncPool()->submitTask(new UpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
    }
    
    /*
     * Get Profanity List. Do not call it directly.
     * @return Config
    */
    public function getProfanity() : Config {
         return new Config($this->getDataFolder() . "profanity.yml", Config::YAML);
    }
    
    /*
     * Initilize the resource in the context.
     * @return void
    */
    private function saveResources(): void {
         if(!file_exists($this->getDataFolder() . "languages/")){
              @mkdir($this->getDataFolder() . "languages/");
         }
         if(!file_exists($this->getDataFolder() . "banned-words.yml")){
         $this->saveResource("banned-words.yml");
         }
         
         foreach($this->getResources() as $file){
               $this->saveResource($file->getFileName());
         }
    }
}