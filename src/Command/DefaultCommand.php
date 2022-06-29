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

namespace ProfanityFilter\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use ProfanityFilter\Utils\Language;
use ProfanityFilter\Loader;

class DefaultCommand extends Command {
     public function __construct(){
          $this->plugin = Loader::getInstance();
     }
     
     /*
      * @param CommandSender $sender
      * @param string $commandLabel
      * @param array $args
      * @return void
     */
     public function execute(CommandSender $sender, string $commandLabel, array $args) :void {
          if(!isset($args[0])){
               $sender->sendMessage($this->language->translateMessage("command-usage"))
          }
     }
}