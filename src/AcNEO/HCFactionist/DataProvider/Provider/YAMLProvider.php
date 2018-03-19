<?php

namespace AcNEO\HCFactionist\DataProvider\Provider;

use pocketmine\IPlayer;
use pocketmine\Player;
use pocketmine\utils\Config;
use AcNEO\HCFactionist\Main;

class YAMLProvider extends BaseProvider implements Provider {

    protected $factionsData;
    protected $usersData;
    protected $plotsData;
    protected $invitesQuery;
    protected $domainsData;
    protected $factionsVault;

    public function __construct(Main $main) {
        parent::__construct($main);
        $this->main = $main;
        $this->factionsData = new Config($this->main->getDataFolder() . "FactionsData.yml", Config::YAML, []);
        $this->usersData = new Config($this->main->getDataFolder() . "UsersData.yml", Config::YAML, []);
        $this->plotsData = new Config($this->main->getDataFolder() . "PlotsData.yml", Config::YAML, []);
        $this->invitesQuery = new Config($this->main->getDataFolder() . "InvitesQuery.yml", Config::YAML, []);
        $this->domainsData = new Config($this->main->getDataFolder() . "DomainsData.yml", Config::YAML, []);
        $this->factionsVault = new Config($this->main->getDataFolder() . "FactionsVault.yml", Config::YAML, []);
        $this->conf = new Config($this->main->getDataFolder() . "Config.yml", Config::YAML);
    }

    public function getProvider() : string {
        return "yaml";
    }

    public function createFaction(string $Faction_name, Player $sender) : bool {
        $this->factionsData->set(strtolower($Faction_name), [
            "name" => strtolower($Faction_name),
            "display" => $Faction_name,
            "motd" => "This is a new faction! Use /f motd <new motd> to set a new motd.",
            "leader" => strtolower($sender->getName()),
            "power" => 5,
            "currency" => 0,
            "level" => 1,
            "exp" => 0,
            "boosterEffect" => [],
            "officers" => [],
            "members" => []
        ]);
        $this->plotsData->set(strtolower($Faction_name), [
            "plots" => [
                "worlds" => [],
                "claimX" => $sender->getX() + $this->conf->get("plots.plot_multiplier"),
                "claimX2" => $sender->getX() - $this->conf->get("plots.plot_multiplier"),
                "claimZ" => $sender->getZ() + $this->conf->get("plots.plot_multiplier"),
                "claimZ2" => $sender->getZ() - $this->conf->get("plots.plot_multiplier")
            ]
        ]);
        $this->domainsData->set(strtolower($Faction_name), [
            "domains" => [
                "DomainsEffect" => [],
                "worlds" => [],
                "claimX" => $sender->getX() + $this->conf->get("domain_multiplier"),
                "claimX2" => $sender->getX() - $this->conf->get("domain_multiplier"),
                "claimZ" => $sender->getZ() + $this->conf->get("domain_multiplier"),
                "claimZ2" => $sender->getZ() - $this->conf->get("domain_multiplier")
            ]
        ]);
        $this->usersData->set(strtolower($sender->getName()), [
            "name" => strtolower($sender->getName()),
            "faction" => strtolower($name),
            "role" => Main::FACTION_LEADER,
            "alias" => Main::FACTION_LEADER,
            "DailyQuest" => 0,
        ]);
        $this->factionsVault->set(strtolower($Faction_name), [
            "vault" => []
        ]);
        $this->save();

        return true;
    }

    public function getFaction(string $faction) : array {
        $name = strtolower($faction);
        if($this->factionsData->get($name) == false) {
            return array();
        }else{
            return $this->factionsData->get($name);
        }
    }

    public function factionExists(string $faction) {
        $i = $this->factionsData->get(strtolower($faction));
        if($i == null){
            return false;
        }else{
            return true;
        }
    }

    public function getNumberOfFactions() : int {
        return count($this->factionsData->getAll());
    }

    public function setMOTD(string $name, string $motd) : bool {
        $this->factionsData->setNested($name . ".motd", $motd);
        $this->save();

        return true;
    }

    public function save() {
        $this->factionsData->save();
        $this->usersData->save();
        $this->plotsData->save();
        $this->invitesQuery->save();
        $this->domainsData->save();
        $this->factionsVault->save();
    }

    public function getFactionMembers(string $faction) : array {
        $factions = strtolower($faction);
        $members = $this->factionsData->get($factions)["members"];
        foreach($members as $member) {
            return $member;
        }
    }

    public function getFactionOfficers(string $faction) : array {
        $factions = strtolower($faction);
        $officers = $this->factionsData->get($factions)["officers"];
        foreach($officers as $officer) {
            return $officer;
        }
    }

    public function removeFaction(string $faction) : bool {
        $members = $this->getFactionMembers(strtolower($faction));
        $member = strtolower($members);
        $officers = $this->getFactionOfficers(strtolower($faction));
        $officer = strtolower($officers);
        $leader = $this->factionsData->get(strtolower($faction) . ".leader");
        $this->usersData->remove($leader);
        $this->usersData->remove($member);
        $this->usersData->remove($officer);
        $this->factionsData->remove(strtolower($faction));
        $this->plotsData->remove(strtolower($faction));
        $this->domainsData->remove(strtolower($faction));
        $this->factionsVault->remove(strtolower($faction));
        $this->save();

        return true;
    }

    public function removePlayerFromFaction(IPlayer $player) : bool {
        $faction = $this->getPlayer($player)["faction"];
        $role = $this->getPlayer($player)["role"];
        if($role == Main::LEADER) {
            $this->removeFaction($faction);
            return true;
        }elseif($role == Main::OFFICER) {
            $power = $this->factionsData->get(strtolower($faction) . ".power");
            $this->factionsData->set(strtolower($faction) . ".power", $power - 6);
            $officers = $this->factionsData->get($faction)["officers"];
            unset($officers[strtolower($player->getName())]);
            $this->factionsData->setNested($faction . ".officers", $officers);
            $this->usersData->remove(strtolower($player->getName()));
            $this->save();
            return true;
        }elseif($role == Main::MEMBER) {
            $power = $this->factionsData->get(strtolower($faction) . ".power");
            $this->factionsData->set(strtolower($faction) . ".power", $power - 5);
            $members = $this->factionsData->get($faction)["members"];
            unset($members[strtolower($player->getName())]);
            $this->factionsData->setNested($faction . ".members", $members);
            $this->usersData->remove(strtolower($player->getName()));
            $this->save();
            return true;
        }
    }

    public function getPlayerRole(string $name) : string {
        $strtolower_name = strtolower($name);
        $role = $this->usersData->get($strtolower_name . ".role");
        return $role;
    }

    public function promotePlayer(IPlayer $sender, string $player) : bool {
        $strtolower_name = strtolower($player);
        $role = $this->getPlayerRole($strtolower_name);
        $strtolower_faction = strtolower($this->getPlayerFaction($strtolower_name));
        if($role == Main::LEADER) {
            return Main::ERROR;
        }elseif($role == Main::OFFICER) {
            return Main::ERROR;
        }elseif($role == Main::MEMBER) {
            $sender_strtolower_name = strtolower($sender->getName());
            $sender_role = $this->getPlayerRole($sender_strtolower_name);
            if($sender_role == Main::OFFICER || $sender_role == Main::LEADER){
                $this->usersData->set($strtolower_name . ".role", Main::OFFICER);
                $this->usersData->set($strtolower_name . ".alias", Main::OFFICER);

                $officers = $this->factionsData->get($strtolower_faction)["officers"];
                $officers += [$strtolower_faction . ".officers" . $strtolower_name];
                yaml_emit($officers);
                $this->factionsData->setNested($strtolower_faction . ".officers", $officers); // need to find a better way...

                $members = $this->factionsData->get($strtolower_faction)["members"];
                unset($members[$strtolower_name]);
                $this->save();
                return true;
            }
        }
    }

    public function demotePlayer(IPlayer $sender, string $player) : bool {}

    public function getPlayer(IPlayer $player) : array {
        $playerName = strtolower($player->getName());
        if($this->usersData->get($playerName) == false) {
            return array();
        }else{
            return $this->usersData->get($playerName);
        }
    }

    public function acceptInvite(IPlayer $player) : bool {
        if(!$this->hasInvite($player)) {
            return false;
        }
        $faction = $this->getPlayer($player)["faction"];
        $this->invitesQuery->remove(strtolower($player->getName()));
        $this->usersData->set(strtolower($player->getName()), [
            "name" => strtolower($player->getName()),
            "faction" => $faction,
            "role" => Main::MEMBER,
            "alias" => Main::MEMBER,
            "DailyQuest" => 0
        ]);
        $members = $this->factionsData->get($faction)["members"];
        $members[] = strtolower($player->getName());
        $this->factionsData->setNested($faction . ".members", $members);
        $this->save();
        return true;
    }

    public function hasInvite(IPlayer $player) : bool {
        if(!$this->invitesQuery->exists(strtolower($player->getName()))) {
            return false;
        }
        return true;
    }

    public function playerIsInFaction(IPlayer $player) : bool {
        if(isset($this->getPlayer($player)["faction"])) {
            return true;
        }else{
            return false;
        }
    }

    public function newInvite(IPlayer $to, IPlayer $from) : bool {
        $this->invitesQuery->set(strtolower($to->getName()), [
            "from" => [
                "by" => strtolower($from->getName()),
                "inviteTo" => $this->getPlayer(strtolower($from->getName()))["faction"]
        ]);
        $this->save();
        return true;
    }

    public function isLeader(string $name) : bool {
        $strtolower_name = strtolower($name);
        $role = $this->usersData->get($strtolower_name . ".role");
        if($role == Main::LEADER) {
            return true;
        }else{
            return false;
        }
    }

    public function isOfficer(string $name) : bool {
        $strtolower_name = strtolower($name);
        $role = $this->usersData->get($strtolower_name . ".role");
        if($role == Main::OFFICER) {
            return true;
        }else{
            return false;
        }
    }
    
    public function isMember(string $name) : bool {
        $strtolower_name = strtolower($name);
        $role = $this->usersData->get($strtolower_name . ".role");
        if($role == Main::MEMBER) {
            return true;
        }else{
            return false;
        }
    }

    public function getPlayerFaction(string $name) : string {
        $strtolower_name = strtolower($name);
        if($this->usersData->exists($strtolower_name)) {
            $faction = $this->usersData->get($name . ".faction");
            return $faction;
        }
        return false;
    }

    public function getFactionPower(string $name) : int {
        $strtolower_name = strtolower($name);
        $power = $this->factionsData->get($strtolower_name . ".power");
        return $power;
    }

    public function getFactionCurrency(string $name) : int {
        $strtolower_name = strtolower($name);
        $currency = $this->factionsData->get($strtolower_name . ".currency");
        return $currency;
    }

    public function getFactionMemberCount(string $name) : int {
        // included officer , leader, and members
        $strtolower_name = strtolower($name);
        $officers_count = count($this->getFactionOfficers($strtolower_name));
        $leader_count = 1;
        $members_count = count($this->getFactionMembers($strtolower_name));
        $all = $leader_count + $officers_count + $members_count;
        return $all;
    }

    public function getFactionLevel(string $name) : int {
        $strtolower_name = strtolower($name);
        $level = $this->factionsData->get($strtolower_name . ".level");
        return $level;
    }

    public function isInFaction(string $name) : bool {
        $strtolower_name = strtolower($name);
    }

    public function setPlots(Player $sender) : bool {
        $name = $sender->getName();
        $strtolower_name = strtolower($name);
        $faction = $this->getPlayerFaction($strtolower_name);
        if($this->isLeader($strtolower_name) == true) {
            $requirement_Power = $this->conf->get("plot.Power_Needed");
            $requirement_Currency = $this->conf->get("plot.Currency_Needed");
            $requirement_Member = $this->conf->get("plot.Member_Needed");
            $requirement_Faction_Level = $this->conf->get("plot.Level_Needed");

            $power = $this->getFactionPower($faction);
            $currency = $this->getFactionCurrency($faction);
            $member = $this->getFactionMemberCount($faction);
            $level = $this->getFactionLevel($faction);
            if($power >= $requirement_Power) {
                if($currency >= $requirement_Currency) {
                    if($member >= $requirement_Member) {
                        if($level >= $requirement_Faction_Level) {
                            $strtolower_faction = strtolower($faction);
                            $x = $sender->getX() + $this->conf->get("plot_multiplier");
                            $x2 = $sender->getX() - $this->conf->get("plot_multiplier");
                            $z = $sender->getZ() + $this->conf->get("plot_multiplier");
                            $z2 = $sender->getZ() - $this->conf->get("plot_multiplier");
                            $this->plotsData->set($faction . ".plots.claimX", $x);
                            $this->plotsData->set($faction . ".plots.claimX2", $x2);
                            $this->plotsData->set($faction . ".plots.claimZ", $z);
                            $this->plotsData->set($faction . ".plots.claimZ2", $z2);
                            $this->plotsData->save();
                            return true;
                        }
                    }
                }
            }
        }
    }

    public function setDomain(Player $sender) : bool {
        $name = $sender->getName();
        $strtolower_name = strtolower($name);
        $faction = $this->getPlayerFaction($strtolower_name);
        if($this->isLeader($strtolower_name) == true) {
            $requirement_Power = $this->conf->get("Domain.Power_Needed");
            $requirement_Currency = $this->conf->get("Domain.Currency_Needed");
            $requirement_Member = $this->conf->get("Domain.Member_Needed");
            $requirement_Faction_Level = $this->conf->get("Domain.Level_Needed");

            $power = $this->getFactionPower($faction);
            $currency = $this->getFactionCurrency($faction);
            $member = $this->getFactionMemberCount($faction);
            $level = $this->getFactionLevel($faction);
            if($power >= $requirement_Power) {
                if($currency >= $requirement_Currency) {
                    if($member >= $requirement_Member) {
                        if($level >= $requirement_Faction_Level) {
                            $strtolower_faction = strtolower($faction);
                            $x = $sender->getX() + $this->conf->get("plot_multiplier");
                            $x2 = $sender->getX() - $this->conf->get("plot_multiplier");
                            $z = $sender->getZ() + $this->conf->get("plot_multiplier");
                            $z2 = $sender->getZ() - $this->conf->get("plot_multiplier");
                            $this->domainsData->set($faction . ".plots.claimX", $x);
                            $this->domainsData->set($faction . ".plots.claimX2", $x2);
                            $this->domainsData->set($faction . ".plots.claimZ", $z);
                            $this->domainsData->set($faction . ".plots.claimZ2", $z2);
                            $this->domainsData->save();
                            return true;
                        }
                    }
                }
            }
        }
    }

    public function domainEffectsArray() {
        $effect = array[DomainEffect::HASTE, DomainEffect::JUMP, DomainEffect::STRENGTH, DomainEffect::SUPPRESSION, DomainEffect::HEAL, DomainEffect::AREA_PROTECTOR];
        foreach($ffect as $effects) {
            return strtolower($effects);
        }
    }

    public function setDomainEffect(Player $sender, string $effect) : bool {
        $strtolower_name = strtolower($sender->getName());
        $faction = $this->getPlayerFaction($strtolower_name);
        $strtolower_faction = strtolower($faction);
        if($this->isLeader($strtolower_name) == true) {
            $requirement_Power = $this->conf->get("Domain.Effect.Power_Needed");
            $requirement_Currency = $this->conf->get("Domain.Effect.Currency_Needed");
            $requirement_Member = $this->conf->get("Domain.Effect.Member_Needed");
            $requirement_Faction_Level = $this->conf->get("Domain.Effect.Level_Needed");
            $strtolower_effect = strtolower($effect);
            if($strtolower_effect == $this->domainEffectsArray()) {
                DomainEffect::setDomainEffect($strtolower_name, strtolower($effect));
                return true;
            }
        }
    }


}
?>