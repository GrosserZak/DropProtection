<?php
declare(strict_types=1);

namespace GrosserZak\DropProtection\Commands;

use Exception;
use GrosserZak\DropProtection\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as G;

class DropProtectionCommand extends Command implements PluginOwned {

    public function __construct(
        private Main $plugin
    ) {
        parent::__construct("dropprotection", "Drop Protection command", "/dropprotection help", ["dropp"]);
        $this->setPermission("dropprotection.command.use");
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if(!$this->testPermissionSilent($sender)) {
            $sender->sendMessage(G::RED . "You dont have the permission to run this command!");
            return;
        }
        $cmd = $args[0] ?? "help";
        switch($cmd) {
            case "help":
                $message = G::GRAY . str_repeat("-", 7) . $this->plugin::PREFIX . G::GRAY . str_repeat("-", 7) . G::EOL
                    . G::GREEN . "set <seconds>" . G::GRAY . ": Sets the drop protection duration" . G::EOL
                    . G::GREEN . "reload" . G::GRAY . ": Reload the config file";
                $sender->sendMessage($message);
                break;
            case "set":
                if(!isset($args[1])) {
                    $sender->sendMessage($this->plugin::PREFIX . G::RED . " Usage: /dropprotection set <seconds>");
                    return;
                }
                if(!is_numeric($args[1]) or $args[1] <= 0 or $args[1] >= 300) {
                    $sender->sendMessage($this->plugin::PREFIX . G::RED . " Seconds must be a numeric value between 0 and 300 excluded!");
                    return;
                }
                $seconds = (int)$args[1];
                $this->plugin->getConfig()->set($this->plugin::DROP_PROTECTION_DURATION_CONFIG_KEY, $seconds);
                $this->plugin->getConfig()->save();
                $this->plugin->checkConfig($sender);
                break;
            case "reload":
                $this->plugin->getConfig()->reload();
                $sender->sendMessage($this->plugin::PREFIX . G::GREEN . " Config file has been reloaded!");
                $this->plugin->checkConfig($sender);
                break;
            default:
                $sender->sendMessage($this->plugin::PREFIX . G::RED . " Unknown subcommand! Run \"/dropprotection help\" for a full list of commands");
        }
    }

    public function getOwningPlugin() : Plugin {
        return $this->plugin;
    }
}
