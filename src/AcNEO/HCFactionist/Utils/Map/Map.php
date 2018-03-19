<?php
namespace AcNEO\HCFactionist\Utils\Map;

use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use AcNEO\HCFactionist\Main;

class Map {

	// ASCII Map
	CONST MAP_WIDTH = 48;
	CONST MAP_HEIGHT = 8;
	CONST MAP_HEIGHT_FULL = 17;
	CONST MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
	CONST MAP_KEY_WILDERNESS = TF::GRAY . "-";
	CONST MAP_KEY_SEPARATOR = TF::AQUA . "+";
	CONST MAP_KEY_OVERFLOW = TF::WHITE . "-" . TF::WHITE; // # ::MAGIC?
    CONST MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Too Many Factions (>" . 107 . ") on this Map.";
    const N = 'N';
    const NE = '/';
    const E = 'E';
    const SE = '\\';
    const S = 'S';
    const SW = '/';
    const W = 'W';
    const NW = '\\';
    
    public function getMap(Player $observer, int $width, int $height, int $inDegrees, int $size = 16) { // No compass
        $to = (int)sqrt($size);
        $centerPs = new Vector3($observer->x >> $to, 0, $observer->z >> $to);
        $map = [];
        $centerFaction = $this->plugin->factionFromPoint($observer->getFloorX(), $observer->getFloorZ());
        $centerFaction = $centerFaction ? $centerFaction : "Wilderness";
        $head = TF::GREEN . " (" . $centerPs->getX() . "," . $centerPs->getZ() . ") " . $centerFaction . " " . TF::WHITE;
        $head = TF::GOLD . str_repeat("_", (($width - strlen($head)) / 2)) . ".[" . $head . TF::GOLD . "]." . str_repeat("_", (($width - strlen($head)) / 2));
        $map[] = $head;
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        $width = $halfWidth * 2 + 1;
        $height = $halfHeight * 2 + 1;
        $topLeftPs = new Vector3($centerPs->x + -$halfWidth, 0, $centerPs->z + -$halfHeight);
        // Get the compass
        $asciiCompass = self::getASCIICompass($inDegrees, TF::RED, TF::GOLD);
        // Make room for the list of names
        $height--;
        /** @var string[] $fList */
        $fList = array();
        $chrIdx = 0;
        $overflown = false;
        $chars = self::MAP_KEY_CHARS;
        // For each row
        for ($dz = 0; $dz < $height; $dz++) {
            // Draw and add that row
            $row = "";
            for ($dx = 0; $dx < $width; $dx++){
                if($dx == $halfWidth && $dz == $halfHeight){
                    $row .= (self::MAP_KEY_SEPARATOR);
                    continue;
                }
                if(!$overflown && $chrIdx >= strlen(self::MAP_KEY_CHARS)) $overflown = true;
                $herePs = $topLeftPs->add($dx, 0, $dz);
                $hereFaction = $this->plugin->factionFromPoint($herePs->x << $to, $herePs->z << $to);
                $contains = in_array($hereFaction, $fList, true);
                if($hereFaction === NULL){
                    $row .= self::MAP_KEY_WILDERNESS;
                } elseif(!$contains && $overflown){
                    $row .= self::MAP_KEY_OVERFLOW;
                } else {
                    if(!$contains) $fList[$chars{$chrIdx++}] = $hereFaction;
                    $fchar = array_search($hereFaction, $fList);
                    $row .= $this->getColorForTo($observer, $hereFaction) . $fchar;
                }
            }
            $line = $row; // ... ---------------
            // Add the compass
            if($dz == 0) $line = $asciiCompass[0] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
            if($dz == 1) $line = $asciiCompass[1] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
            if($dz == 2) $line = $asciiCompass[2] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
            $map[] = $line;
        }
        $fRow = "";
        foreach ($fList as $char => $faction) {
            $fRow .= $this->getColorForTo($observer, $faction) . $char . ": " . $faction . " ";
        }
        if($overflown) $fRow .= self::MAP_OVERFLOW_MESSAGE;
        $fRow = trim($fRow);
        $map[] = $fRow;
        return $map;
    }
    
    public function getColorForTo(Player $player, $faction) {
        if($this->plugin->getPlayerFaction($player->getName()) === $faction){
            return TF::GREEN;
        }
        return TF::LIGHT_PURPLE;
    }
    
    public static function getASCIICompass($degrees, $colorActive, $colorDefault) : array {
        $ret = [];
        $point = self::getCompassPointForDirection($degrees);
        $row = "";
        $row .= ($point === self::NW ? $colorActive : $colorDefault) . self::NW;
        $row .= ($point === self::N ? $colorActive : $colorDefault) . self::N;
        $row .= ($point === self::NE ? $colorActive : $colorDefault) . self::NE;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::W ? $colorActive : $colorDefault) . self::W;
        $row .= $colorDefault . "+";
        $row .= ($point === self::E ? $colorActive : $colorDefault) . self::E;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::SW ? $colorActive : $colorDefault) . self::SW;
        $row .= ($point === self::S ? $colorActive : $colorDefault) . self::S;
        $row .= ($point === self::SE ? $colorActive : $colorDefault) . self::SE;
        $ret[] = $row;
        return $ret;
    }
    
    public static function getCompassPointForDirection($degrees) {
        $degrees = ($degrees - 180) % 360;
        if($degrees < 0)
            $degrees += 360;
        if(0 <= $degrees && $degrees < 22.5)
            return self::N;
        elseif(22.5 <= $degrees && $degrees < 67.5)
            return self::NE;
        elseif(67.5 <= $degrees && $degrees < 112.5)
            return self::E;
        elseif(112.5 <= $degrees && $degrees < 157.5)
            return self::SE;
        elseif(157.5 <= $degrees && $degrees < 202.5)
            return self::S;
        elseif(202.5 <= $degrees && $degrees < 247.5)
            return self::SW;
        elseif(247.5 <= $degrees && $degrees < 292.5)
            return self::W;
        elseif(292.5 <= $degrees && $degrees < 337.5)
            return self::NW;
        elseif(337.5 <= $degrees && $degrees < 360.0)
            return self::N;
        else
            return null;
    }

}
?>