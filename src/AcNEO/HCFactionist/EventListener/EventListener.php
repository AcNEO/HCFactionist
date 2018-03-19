<?php
namespace AcNEO\HCFactionist\EventListener;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\{TextFormat, Config};
use pocketmine\item\Item;
use AcNEO\HCFactionist\Main;
use AcNEO\HCFactionist\Utils\DailyQuest\DailyQuest;

class EventListener implements Listener {

    public function __construct(Main $main) {
        $this->main = $main;
    }

	public function onJoinDailyQuest(PlayerJoinEvent $ev) {
		$name = $ev->getPlayer()->getName();
		if(DailyQuest::isFirstJoin($name)) {
			DailyQuest::registerConfig($name);
		}
	}

}
?>