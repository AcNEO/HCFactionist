<?php
namespace AcNEO\HCFactionist\Utils\Domain;

use AcNEO\HCFactionist\Main;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use AcNEO\HCFactionist\DataProvider\DataProvider;

class DomainEffect {

    const HASTE = "Haste";
    const JUMP = "Jump";
    const STRENGTH = "Strength";
    const SUPPRESSION = "Suppression";
    const HEAL = "Heal";
    const AREA_PROTECTOR = "Area_Protector";


    public function __construct(Main $main) {
        $this->main = $main;
        $this->conf = new Config($this->main->getDataFolder() . "Config.yml", Config::YAML);
    }

    public function setDomainEffect(string $sender, string $id) {
        $strtolower_sender_name = strtolower($sender);
        $faction = DataProvider::getPlayerFaction($strtolower_sender_name);
        $strtolower_faction = strtolower($faction);
        $leader = $this->factionsData->get($strtolower_faction . ".leader");
        $officers = DataProvider::getFactionOfficers($strtolower_faction);
        $members = DataProvider::getFactionMembers($strtolower_faction);

        $effect = $this->getDomainData($strtolower_faction, 1);
        $x = $this->getDomainData($strtolower_faction, 2);
        $x2 = $this->getDomainData($strtolower_faction, 3);
        $z = $this->getDomainData($strtolower_faction, 4);
        $z2 = $this->getDomainData($strtolower_faction, 5);
        if(strtolower($id) == strtolower(DataProvider::domainEffectsArray())) {
            $requirement_power = $this->conf->get("Domains.Effect." . $id . ".power");
            $requirement_currency = $this->conf->get("Domains.Effect." . $id . ".currency");
            $requirement_member = $this->conf->get("Domains.Effect." . $id . ".member");
            $requirement_level = $this->conf->get("Domains.Effect." . $id . ".level");

            $fac_power = DataProvider::getFactionPower($strtolower_faction);
            $fac_currency = DataProvider::getFactionCurrency($strtolower_faction);
            $fac_member = DataProvider::getFactionMemberCount($strtolower_faction);
            $fac_level = DataProvider::getFactionLevel($strtolower_faction);
            if($fac_level >= $requirement_level) {
                if($fac_member >= $requirement_member) {
                    if($fac_power >= $requirement_power) {
                        if($fac_currency >= $requirement_currency) {
                            $new_power = $fac_power - $requirement_power;
                            $new_currency = $fac_currency - $requirement_currency;
                            DataProvider::setFactionPower($strtolower_faction, $new_power);
                            DataProvider::setFactionCurrency($strtolower_faction, $new_currency);
                            $i = DataProvider::getfactionDomainData($strtolower_faction)["domains.DomainsEffect"];
                            $i += [$strtolower_faction . ".domains.DomainsEffect." . strtolower($id)];
                            DataProvider::save();
                        }
                        return Main::ERROR;
                    }
                    return Main::ERROR;
                }
                return Main::ERROR;
            }
            return Main::ERROR;
        }
        return Main::ERROR;
    }

    public function getDomainData(string $faction, int $type) { // to lazy to make a new function etc.
        if($type = 1){
            $i = $this->domainsData->get(strtolower($faction) . ".domains.DomainsEffect");
            foreach($i as $ii) {
                return $ii;
            }
        }elseif($type = 2){
            return $this->domainsData->get(strtolower($faction) . ".domains.claimX");
        }elseif($type = 3){
            return $this->domainsData->get(strtolower($faction) . ".domains.claimX2");
        }elseif($type = 4){
            return $this->domainsData->get(strtolower($faction) . ".domains.claimZ");
        }elseif($type = 5){
            return $this->domainsData->get(strtolower($faction) . ".domains.claimZ2");
        }
    }

}
?>