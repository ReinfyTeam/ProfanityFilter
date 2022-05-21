<?php

namespace xqwtxon\HiveProfanityFilter\type;


class block-with-message implements Listener {
    
    public function __construct(private Main $main){
        //NOOP
    }
     public function onChat(PlayerChatEvent $ev) :void {
        foreach(Main::profanity_config()->get("banned-words") as $words){
             
         )};
     }
}