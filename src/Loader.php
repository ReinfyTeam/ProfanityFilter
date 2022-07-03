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

namespace xqwtxon\ProfanityFilter;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\permission\Permission;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissionManager;
use xqwtxon\ProfanityFilter\Command\DefaultCommand;
use xqwtxon\ProfanityFilter\EventListener;
use xqwtxon\ProfanityFilter\Utils\Language;
use xqwtxon\ProfanityFilter\Tasks\UpdateTask;
use function yaml_parse;
use DateTime;
use DateInterval;

class Loader extends PluginBase {
    
    /** @var Loader $instance **/
    private static Loader $instance;
    
    /** @var array $punishment **/
    public array $punishment = [];
    
    
    public function onLoad() :void {
        Loader::$instance = $this;
        $this->checkConfig();
        $this->checkUpdate();
        (new Language())->init();
        $this->saveResources();
        $this->loadPermission();
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
	    $lang = new Language();
	    $pluginConfig = yaml_parse(stream_get_contents($pluginConfigResource));
	    fclose($pluginConfigResource);
	    $config = $this->getConfig();
		
	    if($pluginConfig == false) {
	    	$log->critical("Invalid Configuration Syntax, Please remove your update the plugin.");
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
    public function formatMessage(string $message, ?Player $player = null) : string { // TODO: Move this in the event class
         $message = str_replace("{type}", $this->getConfig()->get("punishment-type") . "ed", $message);
         
         if($player === null) return $message;
         
         $message = str_replace("{player_name}", $player->getName(), $message);
         $message = str_replace("{player_ping}", $player->getNetworkSession()->getPing(), $message);
         
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
         $this->saveResource("languages/english.yml");
         if(!file_exists($this->getDataFolder() . "banned-words.yml")){
         $this->saveResource("banned-words.yml");
         }
         
         foreach($this->getResources() as $file){
               $this->saveResource($file->getFileName());
         }
    }
    
    public function getDuration() {
         if($this->getConfig()->get("ban-duration") === "Forever"){
              return null;
         } else {
               return $this->stringToTimestamp($this->getConfig()->get("ban-duration"));
         }
    }
    /*
     * Convert String to Timestamp
     *
     * @param string $string
     * @return ?array
    */
    private function stringToTimestamp(string $string): ?array
    {
        /**
         * Rules:
         * Integers without suffix are considered as seconds
         * "s" is for seconds
         * "m" is for minutes
         * "h" is for hours
         * "d" is for days
         * "w" is for weeks
         * "mo" is for months
         * "y" is for years
         */
        if (trim($string) === "") {
            return null;
        }
        $t = new DateTime();
        preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $string, $found);
        if (count($found[0]) < 1) {
            return null;
        }
        $found[2] = preg_replace("/[^0-9]/", "", $found[0]);
        foreach ($found[2] as $k => $i) {
            switch ($c = $found[1][$k]) {
                case "y":
                case "w":
                case "d":
                    $t->add(new DateInterval("P" . $i . strtoupper($c)));
                    break;
                case "mo":
                    $t->add(new DateInterval("P" . $i . strtoupper(substr($c, 0, strlen($c) - 1))));
                    break;
                case "h":
                case "m":
                case "s":
                    $t->add(new DateInterval("PT" . $i . strtoupper($c)));
                    break;
                default:
                    $t->add(new DateInterval("PT" . $i . "S"));
                    break;
            }
            $string = str_replace($found[0][$k], "", $string);
        }
        return [$t, ltrim(str_replace($found[0], "", $string))];
    }
    
    private function loadPermission() :void {
         $this->registerPermission(($this->getConfig()->get("command-permission") ?? "profanityfilter.command"));
         $this->registerPermission(($this->getConfig()->get("bypass-permission") ?? "profanityfilter.bypass"));
    }
    
    /*
     * Register Permission on Plugin
     * Custom Permission in Config.yml
     *
     * @param $perm
     * @return void
    */
    private function registerPermission($perm) :void {
         $permission = new Permission($perm);
         $permManager = PermissionManager::getInstance();
         $permManager->addPermission($permission);
         $permManager->getPermission(DefaultPermissions::ROOT_OPERATOR)->addChild($permission->getName(), true);
    }
}