<?php

declare(strict_types=1);

namespace xqwtxon\HiveProfanityFilter;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use function rename;

class Main extends PluginBase{
    
    
    public static function get_profanity_config(string $option){
        $config = new Config($this->getDataFolder() . "/banned-words.yml", Config::YAML);
        
        return $config->get($option);
    }
    
     public function onEnable() :void {
         $ver = $this->getConfig()->get("config-version");
         $log = $this->getLogger();
         $config = $this->getConfig();
         if (!$ver == "1.0.0"){
             @rename($this->getDataFolder() . "/config.yml", $this->getDataFolder() . "/old-config.yml")
             $log->notice("Your config is outdated. Your old config will renamed as 'old-config.yml'");
         }
         if 
         if (!isset($this->config->get("type"))){
             $config->set("type", "block-with-message");
             $log->notice("The ProfanityType is unspecified. As default we set it to 'block-with-message' value.");
             $this->saveConfig();
         }
         switch($this->getConfig()->get("type")){
             case "block":
                 break;
             case "block-with-message":
                 break;
             case "hide":
                 break;
             default:
                 break;
         }
         if (!$config->get("multiworld-support") == true){
           if (!isset($config->get("message-prefix"))){
             $config->set("message-prefix", "{NAME} §r§l>§r {MESSAGE}");
             $log->notice("The Message Prefix is unspecified. As default we set it to '{NAME} §r§l>§r {MESSAGE}' value.");
             return;
            }
         }
         
         $other_config = new Config($this->getDataFolder() . "/banned-words.yml", Config::YAML);
         
         if (!isset($other_config->get("banned-words"))){
             
         }
     }
}
