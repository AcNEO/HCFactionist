<?php
namespace AcNEO\HCFactionist\Utils\DailyQuest;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\{TextFormat, Config};
use pocketmine\item\Item;
use AcNEO\HCFactionist\Main;

class DailyQuest {

    public $cooldown = 86400;
    public $complete;
    public $already_complete;

    public function __construct(Main $main) {
        $this->conf = new Config($this->main->getDataFolder() . "Config.yml", Config::YAML);
        $this->complete = $this->conf->get("DailyQuest.Claimed");
        $this->already_complete = $this->conf->get("DailyQuest.Already_Claimed");
    }
	
	public function getPlayerConfig(string $player) {
        $i = new Config($this->getDataFolder() . "players/" . strtolower($player) . ".json", Config::JSON);
		return $i;
	}
	
	public function registerConfig(string $player) : bool {
		$i = new Config($this->getDataFolder() . "players/" . strtolower($player) . ".json", Config::JSON, [
        "time" => time(),
        "quest_id" => 0,
        ]);
        $i->save();
        return true;
	}
	
	public function isFirstJoin(string $player) {
		return !file_exists($this->getDataFolder() . "players/" . strtolower($player) . ".json");
	}
	
    public function dailyQuest(Player $sender) {
		if($sender instanceof Player) {
			$cfg = $this->getPlayerConfig($sender->getName());
			if((time() - $cfg->get("time")) >= 86400) {
				$sender->sendMessage($this->claimed);
                $name = $sender->getName();

				$time = (time() + $this->cooldown);
                $cfg->set("time", $time);

                $random_quest = mt_rand(1, 20);
                $this->sendQuest($sender, $random_quest);
                
			} else {
				$sender->sendMessage($this->already_complete);
			}
		}
    }

    public function sendQuest(Player $sender, int $quest) {
        // code
    }

}
?>