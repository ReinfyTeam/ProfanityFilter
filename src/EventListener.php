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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use ProfanityFilter\Loader;
use ProfanityFilter\Utils\Language;

class EventListener implements Listener {
    
    private string $type;
    
    public function __construct(string $type){
        $this->plugin = Loader::getInstance();
        $this->type = $type;
    }
    
    /*
     * When player chat.
     *
     * @param PlayerChatEvent $ev
     * @return void
    */
    public function onChat(PlayerChatEvent $ev): void {
        $message = $ev->getMessage();
        $player = $ev->getPlayer();
        $words = $this->plugin->getProfanity()->get("banned-words");
        if(PluginAPI::detectProfanity($message, $words)){
            switch($this->type){
                case "block":
                    $ev->cancel();
                    $player->sendMessage($this->plugin->getConfig()->get("block-message"));
                    break;
                case "hide":
                    $ev->setMessage(PluginAPI::removeProfanity($message, $words));
                    break;
                default:
                    throw new \Excemption("Cannot Identify the type of profanity in config.yml");
                    break;
            }
            
            if($this->plugin->punishment === $this->plugin->getConfig()->get("max-violations")){
                switch($this->plugin->getConfig()->get("punishment-type")){
                    case "ban":
                        $this->plugin->punishment = 0;
                        $player->getServer()->getNameBans()->
                        $player->kick($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message")));
                        break;
                    case "kick":
                        $this->plugin->punishment = 0;
                        $player->kick($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message")));
                        break;
                    default:
                        throw new \Excemption("Cannot Identify the type of punishment in config.yml");
                        break;
                }
            } else {
                $this->plugin->punishment++;
            }
        }
    }
    
    /*
     * When player chats command.
     *
     * @param PlayerCommandPreprocessEvent $ev
     * @return void
    */
    public function onCommand(PlayerCommandPreprocessEvent $ev) :void {
         $message = $ev->getMessage();
         $player = $ev->getPlayer();
         $words = $this->plugin->getProfanity()->get("banned-words");
         if(PluginAPI::detectProfanity($message, $words)){
              switch($this->type){
                case "block":
                    $ev->cancel();
                    $player->sendMessage($this->plugin->getConfig()->get("block-message"));
                    break;
                case "hide":
                    $ev->setMessage(PluginAPI::removeProfanity($message, $words));
                    break;
                default:
                    throw new \Excemption("Cannot Identify the type of profanity in config.yml");
                    break;
            }
            
            if($this->plugin->punishment === $this->plugin->getConfig()->get("max-violations")){
                switch($this->plugin->getConfig()->get("punishment-type")){
                    case "ban":
                        $this->plugin->punishment = 0;
                        $player->getServer()->getNameBans()->
                        $player->kick($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message")));
                        break;
                    case "kick":
                        $this->plugin->punishment = 0;
                        $player->kick($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message")));
                        break;
                    default:
                        throw new \Excemption("Cannot Identify the type of punishment in config.yml");
                        break;
                }
            } else {
                $this->plugin->punishment++;
            }
         }
    }
}