<?php
namespace AcNEO\HCFacionist;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;

class Main extends PluginBase implements Listener {

    const LEADER = "Leader";
    const OFFICER = "Officer";
    const MEMBER = "Member";

    const ERROR = -1;
    
    public function onEnable() {
        // Code to execute when the plugin got enabled
        // this->getServer()->getPluginManager()->registerEvents(this,this)
    }
    
    public function onLoad() {
        // Code to execute when the plugin got loaded
    }
    
    public function onDisable() {
        // Cde to execute just before the plugin gets disable
    }


}
?>