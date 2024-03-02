<?php

namespace Nay\AntiDC;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener {

    /** @var array */
    private $playerIPs = [];

    /** @var array */
    private $whitelist = [];

    /** @var array */
    private $ipWhitelist = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->loadData();
    }

    public function onDisable(): void {
        $this->saveData();
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $ip = $player->getNetworkSession()->getIp();
        $name = strtolower($player->getName());
        if ($this->isWhitelisted($name) || $this->isIPWhitelisted($ip)) {
            return;
        }
        if ($this->isDuplicateAccount($name, $ip)) {
            $duplicateName = $this->getDuplicateAccount($name, $ip);
            $player->kick("Vous ne pouvez pas rejoindre le serveur car vous êtes le double-compte de $duplicateName.", false);
            return;
        }
        $this->addPlayerIP($name, $ip);        
    }

    private function loadData() {
        $this->whitelist = $this->getConfig()->get("whitelist", []);
        $this->ipWhitelist = $this->getConfig()->get("ip-whitelist", []);
        $this->playerIPs = $this->getConfig()->get("player-ips", []);
    }

    private function saveData() {
        $this->getConfig()->set("whitelist", $this->whitelist);
        $this->getConfig()->set("ip-whitelist", $this->ipWhitelist);
        $this->getConfig()->set("player-ips", $this->playerIPs);
        $this->getConfig()->save();
    }

    private function isWhitelisted(string $name): bool {
        return in_array(strtolower($name), $this->whitelist);
    }

    private function isIPWhitelisted(string $ip): bool {
        return in_array($ip, $this->ipWhitelist);
    }

private function isDuplicateAccount(string $name, string $ip): bool {
        return isset($this->playerIPs[$ip]) && in_array($name, $this->playerIPs[$ip]);
    }

    private function getDuplicateAccount(string $name, string $ip): ?string {
        foreach ($this->playerIPs[$ip] as $duplicateName) {
            if ($duplicateName !== $name) {
               return $duplicateName;
            }
        }
        return null;
    }

 private function addPlayerIP(string $name, string $ip) {
    $lowercaseName = strtolower($name);
    if (!isset($this->playerIPs[$ip]) || !in_array($lowercaseName, $this->playerIPs[$ip])) {
        $this->playerIPs[$ip][] = $lowercaseName;
     }
  }
}
