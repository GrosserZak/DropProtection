<?php
declare(strict_types=1);

namespace GrosserZak\DropProtection;

use GrosserZak\DropProtection\Commands\DropProtectionCommand;
use GrosserZak\DropProtection\Listener\EventListener;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as G;

class Main extends PluginBase {

    public const MAX_DROP_PROTECTION_DURATION = 6000;

    public const DEFAULT_DROP_PROTECTION_DURATION = 5;

    public const ONE_SECOND_IN_TICKS = 20;

    public const PREFIX = G::DARK_GRAY . "[" . G::DARK_AQUA . "Drop" . G::AQUA . "Protection" . G::DARK_GRAY . "]";

    public const DROP_PROTECTION_STRING_TAG = "Owner";

    public const DROP_PROTECTION_DURATION_CONFIG_KEY = "dropProtectionDuration";

    public const ERROR_MSG = G::RED . "Warning! The duration was set over 5 minutes! Force resetting...";

    public const SUCCESS_MSG = G::GREEN . "Drop Protection duration has been set to " . G::YELLOW . "{0} second{1}" . G::GREEN . "!";

    public function onEnable() : void {
        $this->saveDefaultConfig();
        $this->checkConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register($this->getName(), new DropProtectionCommand($this));
    }

    public function checkConfig(CommandSender $sender = null) : void {
        $seconds = $this->getConfig()->get(self::DROP_PROTECTION_DURATION_CONFIG_KEY);
        if($seconds > self::MAX_DROP_PROTECTION_DURATION) {
            $msg = self::ERROR_MSG;
            $this->getConfig()->set(self::DROP_PROTECTION_DURATION_CONFIG_KEY, self::DEFAULT_DROP_PROTECTION_DURATION);
            $this->getConfig()->save();
        } else {
            $msg = self::SUCCESS_MSG;
        }
        $msg = str_replace(["{0}", "{1}"], [$seconds, ($seconds > 1 ? "s" : "")], $msg);
        $this->getLogger()->warning($msg);
        $sender?->sendMessage(self::PREFIX . " " . $msg);
    }

}