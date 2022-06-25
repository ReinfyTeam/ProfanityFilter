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
 * @author xqwtxib
 * @link http://xqwtxon.ml/
 *
*/

declare(strict_types=1);

namespace xqwtxon\HiveProfanityFilter\utils;

use xqwtxon\HiveProfanityFilter\Loader;
use xqwtxon\HiveProfanityFilter\utils\ConfigManager;
use xqwtxon\HiveProfanityFilter\utils\LanguageManager;
use pocketmine\player\Player;
use xqwtxon\HiveProfanityFilter\libs\jojoe77777\FormAPI\SimpleForm;
use xqwtxon\HiveProfanityFilter\libs\jojoe77777\FormAPI\CustomForm;
use xqwtxon\HiveProfanityFilter\libs\jojoe77777\FormAPI\ModalForm;
use pocketmine\utils\TextFormat;

class FormManager {
	public function __construct(){
		$this->lang = new LanguageManager();
		$this->config = new ConfigManager();
		$this->plugin = Loader::getInstance();
	}
	
	public function manageProfanity($player){
		$form = new SimpleForm(function(Player $player, $data){
			if($data === null) return;
			switch($data){
				case 0:
					$this->viewProfanity($player);
					break;
				case 1:
					$this->changeProfanity($player);
					break;
				case 3:
					break;
			}
		});
		$form->setTitle($this->lang->translateMessage("ui-pf-manage-profanity-title"));
		$form->setContent($this->lang->translateMessage("ui-pf-manage-description"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-1"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-2"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-3"));
		$player->sendForm($form);
	}
	
	public function viewProfanity($player){
		$form = new SimpleForm(function(Player $player, $data){
			if($data === null) return;
			switch($data){
				case 0:
					$this->manageProfanity($player);
					break;
			}
		});
		$form->setTitle($this->lang->translateMessage("ui-pf-manage-profanity-title"));
		foreach($this->config->profanityGet("banned-words") as $word){
			$form->setContent("- ". TextFormat::RED . $word);
		}
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-return"));
		$player->sendForm($form);
	}
	
	public function typeProfanity($player){
		$form = new SimpleForm(function(Player $player, $data){
			if($data === null) return;
			switch($data){
				case 0:
					$sender->chat("/pf type hide");
					break;
				case 1:
					$sender->chat("/pf type block");
					break;
				case 2:
					$sender->chat("/pf type block-with-message");
					break;
				case 3:
					$this->manageProfanity($sender);
					break;
			}
		});
		$form->setTitle($this->lang->translateMessage("ui-pf-manage-profanity-title"));
		$form->setContent($this->lang->translateMessage("ui-pf-manage-type-profanity-description"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-4"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-5"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-5"));
		$form->addButton($this->lang->translateMessage("ui-pf-manage-button-return"));
		$player->sendForm($form);
	}
}