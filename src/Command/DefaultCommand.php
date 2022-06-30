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

namespace ProfanityFilter\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use ProfanityFilter\Utils\Language;
use ProfanityFilter\Utils\Forms\SimpleForm;
use ProfanityFilter\Loader;

class DefaultCommand extends Command {
     
     /** @var Loader $plugin **/
     private Loader $plugin;
     
     public function __construct(){
          $this->plugin = Loader::getInstance();
          $this->language = new Language();
          parent::__construct("profanityfilter", "ProfanityFilter Management", "/profanityfilter <help/subcommand>", ["pf"]);
     }
     
     /*
      * @param CommandSender $sender
      * @param string $commandLabel
      * @param array $args
      * @return void
     */
     public function execute(CommandSender $sender, string $commandLabel, array $args) :void {
          if(!isset($args[0])){
               $sender->sendMessage($this->language->translateMessage("profanity-command-usage-execute"));
               return;
          }
          
          switch($args[0]){
               case "help":
                    $sender->sendMessage($this->language->translateMessage("help-title"));
                    $sender->sendMessage($this->language->translateMessage("help-subtitle"));
                    foreach($this->language->translateMessage("help-page") as $command){
                         $sender->sendMessage("- " . $command);
                    }
                    break;
               case "ui":
               case "gui":
               case "form":
                    if(!$sender instanceof Player){
                         $sender->sendMessage($this->language->translateMessage("profanity-command-only-ingame"));
                    } else {
                         $this->sendForm($sender);
                    }
                    break;
               case "info":
               case "credits":
                    $sender->sendMessage($this->language->translateMessage("credits-title"));
                    $sender->sendMessage($this->language->translateMessage("credits-subtitle"));
                    $sender->sendMessage($this->language->translateMessage("credits-description"));
                    foreach($this->plugin->getDescription()->getAuthors() as $author){
                         $sender->sendMessage("- " . $author);
                    }
                    break;
               case "list":
               case "words":
               case "banned-words":
                    $sender->sendMessage($this->language->translateMessage("banned-words-description"));
                    foreach($this->plugin->getProfanity()->get("banned-words") as $word){
                         $sender->sendMessage("- " . $word);
                    }
                    $sender->sendMessage($this->language->translateMessage("banned-words-description-2"));
                    break;
               default:
                    $sender->sendMessage($this->language->translateMessage("profanity-command-usage-execute"));
                    break;
          }
     }
     
     /*
      * Profanity Form Interface.
      * @param Player $player
      * @return void
     */
     private function sendForm(Player $player) :void {
          $form = new SimpleForm(function(Player $player, $data){
               if($data === null) return;
               
               switch($data){
                    
               }
          });
          
          $form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
          $form->setContent($this->language->translateMessage("ui-pf-manage-description"));
          $form->addButton($this->language->translateMessage("ui-pf-manage-button-1"));
          $form->addButton($this->language->translateMessage("ui-pf-manage-button-exit"));
          $player->sendForm($form);
     }
     
     /*
      * Profanity Form Interface.
      * @param Player $player
      * @return void
     */
     private function viewList(Player $player) :void {
          $form = new SimpleForm(function(Player $player, $data){
               if($data === null) return;
               
               switch($data){
                    case 0:
                         $this->sendForm($player);
                         break;
               }
          });
          
          $form->setTitle($this->language->translateMessage("ui-pf-manage-title"));
          $form->addButton($this->language->translateMessage("ui-pf-manage-button-return"));
          $form->addButton($this->plugin->getProfanity()->get("banned-words"));
          $player->sendForm($form);
     }
}