<?php
namespace AcNEO\HFactionist\Utils\Plot;

use pocketmine\block\Gold;
use pocketmine\block\Emerald;
use pocketmine\math\Vector3;
use AcNEO\HCFactionist\DataProvider\DataProvider;
use AcNEO\HCFactionist\Main;

class Plots {

    public function __construct(Main $main) {
        $this->main = $main;
        $this->conf = new Config($this->main->getDataFolder() . "Config.yml", Config::YAML);
    }

    public function newPlot(string $faction, int $x1, int $z1, int $x2, int $z2, string $world) : bool {
		if(DataProvider::factionExists(strtolower($faction)) == true) {
            DataProvider::newPlot(strtolower($faction), int $x1, int $z1, int $x2, int $z2, string $world);
            return true;
        }
	}
	public function drawPlot(Player $sender, string $faction, int $x, int $y, int $z, Level $world, int $size) : bool {

        $i = ($size - 1) / 2;
        $ii = $this->conf->get("plots.plot_multiplier");
        $block_center = new Gold();
        $block_around_center = new Emerald();
        
		if($this->cornerIsInPlot($x + $ii, $z + $ii, $x - $ii, $z - $ii)) {

			$claimedBy = $this->factionFromPoint($x, $z);
            $power_claimedBy = $this->getFactionPower($claimedBy);
            $power_sender = $this->getFactionPower($faction);
            
            if($this->conf->get("plots.EnableOverClaim")){
                if($power_sender < $power_claimedBy){
                    $sender->sendMessage(TextFormat::GOLD."- ".TextFormat::RED."This area is aleady claimed by $claimedBy with $power_claimedBy STR. Your faction has $power_sender power. You don't have enough power to overclaim this plot.");
                } else {
                    $sender->sendMessage(TextFormat::GOLD."- ".TextFormat::RED."This area is aleady claimed by $claimedBy with $power_claimedBy STR. Your faction has $power_sender power. Type /f overclaim to overclaim this plot if you want.");
                }
                return false;
            } else {
			    $sender->sendMessage(TextFormat::GOLD."- ".TextFormat::RED."Overclaiming is disabled in this server.");
			    return false;
            }
		}
		$world->setBlock(new Vector3($x + $ii, $y, $z + $ii), $block_center);
        $world->setBlock(new Vector3($x - $ii, $y, $z - $ii), $block_center);
        //$surround_cal_1 = ($x + 1) + $ii;
        //$surround_cal_2 = ($x - 1) - $ii;
        //$surround_cal_3 = ($z + 1) + $ii;
        //$surround_cal_4 = ($z - 1) - $ii;

        //$world->setBlock(new Vector3($surround_cal_1, $y, $surround_cal_3), $block_around_center);
        //$world->setBlock(new Vector3($surround_cal_2, $y, $surround_cal_4), $block_around_center);
        
		$this->newPlot($faction, $x + $ii, $z + $ii, $x - $ii, $z - $ii);
		return true;
	}
	
	public function isInPlot(Player $player) {
		$x = $player->getFloorX();
        $z = $player->getFloorZ();
        DataProvider::getPlotsData()->
        // todo
		$result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return empty($array) == false;
	}
   
	
	public function factionFromPoint($x,$z) {
		$result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return $array["faction"];
	}
   
	
	public function inOwnPlot($player) {
		$playerName = $player->getName();
		$x = $player->getFloorX();
		$z = $player->getFloorZ();
		return $this->getPlayerFaction($playerName) == $this->factionFromPoint($x, $z);
	}
	
	public function pointIsInPlot($x,$z) {
		$result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return !empty($array);
	}
	
	public function cornerIsInPlot($x1, $z1, $x2, $z2) {
		return($this->pointIsInPlot($x1, $z1) || $this->pointIsInPlot($x1, $z2) || $this->pointIsInPlot($x2, $z1) || $this->pointIsInPlot($x2, $z2));
	}
	
}
?>