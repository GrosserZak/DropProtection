<?php
declare(strict_types=1);

namespace GrosserZak\DropProtection\Listener;

use GrosserZak\DropProtection\Main;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class EventListener implements Listener {

    public function __construct(
        private Main $plugin
    ) {}

    public function onDrop(PlayerDropItemEvent $ev) : void {
        $item = $ev->getItem();
        $itemNbt = $item->getNamedTag();
        $itemNbt->setTag($this->plugin->getName(),
            CompoundTag::create()->setString($this->plugin::DROP_PROTECTION_STRING_TAG, $ev->getPlayer()->getName())
        );
        $item->setNamedTag($itemNbt);
    }

    public function onPickup(EntityItemPickupEvent $ev) {
        $player = $ev->getEntity();
        $itemEntity = $ev->getOrigin();
        if($player instanceof Player and $itemEntity instanceof ItemEntity) {
            $item = $itemEntity->getItem();
            $itemNbt = $item->getNamedTag();
            if(($itemOwner = $itemNbt->getCompoundTag($this->plugin->getName())?->getString($this->plugin::DROP_PROTECTION_STRING_TAG)) !== null and $itemOwner !== $player->getName()) {
                $duration = $this->plugin->getConfig()->get($this->plugin::DROP_PROTECTION_DURATION_CONFIG_KEY, $this->plugin::DEFAULT_DROP_PROTECTION_DURATION) * $this->plugin::ONE_SECOND_IN_TICKS;
                if($itemEntity->getDespawnDelay() <= ($this->plugin::MAX_DROP_PROTECTION_DURATION - $duration)) {
                    $newNbt = $itemEntity->getItem()->getNamedTag();
                    $newNbt->removeTag($this->plugin->getName());
                    $itemEntity->getItem()->setNamedTag($newNbt);
                }
                $ev->cancel();
            }
        }
    }

}
