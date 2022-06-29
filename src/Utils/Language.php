<?php

namespace ProfanityFilter\Utils;

use pocketmine\utils\Config;
use ProfanityFilter\Loader;

class Language {
  
  /** @var Loader $plugin **/
  private Loader $plugin;
  
  public function __construct(){
    $this->plugin = Loader::getInstance();
  }
  
  public function getLanguage() : Config {
    return (new Config($this->plugin->getDataFolder() . "language/" . $this->getSelectedLanguage() . ".yml"));
  }
  
  public function getSelectedLanguage() :string {
    return $this->plugin->getConfig()->get("lang");
  }
  
  /*
   * Translate Message from Language Configuration
   * Do not call ot directly.
   *
   * @param string $message
   * @return string
  */
  public function translateMessage(string $option) :string  {
    $lang = $this->getLanguage();
    
    /** Check if selected language is missing. **/
    if(is_null($this->getLanguage())) throw new \Exception("Missing file in " . $this->plugin->getDataFolder() . "language/" . $this->getSelectedLanguage() . ".yml");
    
    /** Check if option is exist. **/
    if(!isset($lang->get($option))) throw new \Exception("Trying to access on null.")
    
    return $lang->get($option);
  }
  
  protected function init() :void {
    if(!file_exist($this->plugin->getDataFolder() . "language/" . $this->getSelectedLanguage() . ".yml")){
      $this->plugin->saveResource("languages/" . $this->getSelectedLanguage() . ".yml");
    } else {
      $this->getLanguage()->save();
    }
  }
}