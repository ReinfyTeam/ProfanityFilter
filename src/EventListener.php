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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use xqwtxon\ProfanityFilter\Loader;
use xqwtxon\ProfanityFilter\Utils\Language;
use xqwtxon\ProfanityFilter\Utils\PluginUtils;

class EventListener implements Listener {
    
    private string $type;
    
    public function __construct(string $type){
        $this->plugin = Loader::getInstance();
        $this->type = $type;
        $this->duration = Loader::getInstance()->getDuration();
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
                    $player->sendMessage(PluginUtils::colorize($this->plugin->getConfig()->get("block-message")));
                    break;
                case "hide":
                    $ev->setMessage(PluginAPI::removeProfanity($message, $words));
                    break;
                default:
                    throw new \Excemption("Cannot Identify the type of profanity in config.yml");
                    break;
            }
            
            if(($this->plugin->punishment[$player->getName()] ?? 0) === $this->plugin->getConfig()->get("max-violations")){
                switch($this->plugin->getConfig()->get("punishment-type")){
                    case "ban":
                        $this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ?? 0;
                        $player->getServer()->getNameBans()->addBan($player->getName(), "Profanity", $this->duration[0], $player->getServer()->getName());
                        $player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
                        break;
                    case "kick":
                        $this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ?? 0;
                        $player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
                        break;
                    default:
                        throw new \Excemption("Cannot Identify the type of punishment in config.yml");
                        break;
                }
            } else {
                $this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ? $this->plugin->punishment[$player->getName()] + 1 : 1;
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
                    $player->sendMessage(PluginUtils::colorize($this->plugin->getConfig()->get("block-message")));
                    break;
                case "hide":
                    $ev->setMessage(PluginAPI::removeProfanity($message, $words));
                    break;
                default:
                    throw new \Excemption("Cannot Identify the type of profanity in config.yml");
                    break;
            }
            
            if(($this->plugin->punishment[$player->getName()] ?? 0) === $this->plugin->getConfig()->get("max-violations")){
                switch($this->plugin->getConfig()->get("punishment-type")){
                    case "ban":
                        $this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ?? 0;
                        $player->getServer()->getNameBans()->addBan($player->getName(), "Profanity", $this->duration[0], $player->getServer()->getName());
                        $player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
                        break;
                    case "kick":
                        $this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ?? 0;
                        $player->kick(PluginUtils::colorize($this->plugin->formatMessage($this->plugin->getConfig()->get("kick-message"))));
                        break;
                    default:
                        throw new \Excemption("Cannot Identify the type of punishment in config.yml");
                        break;
                }
            } else {
                $this->plugin->punishment[$player->getName()] = isset($this->plugin->punishment[$player->getName()]) ? $this->plugin->punishment[$player->getName()] + 1 : 1;
            }
        }
    }
}