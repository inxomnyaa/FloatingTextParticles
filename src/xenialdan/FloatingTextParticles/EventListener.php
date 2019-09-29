<?php

namespace xenialdan\FloatingTextParticles;

use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\WritableBook;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\FloatingTextParticles\other\EditHighlightTask;

class EventListener implements Listener
{
    /** @var Loader */
    public $owner;

    public function __construct(Plugin $plugin)
    {
        $this->owner = $plugin;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event)
    {
        /** @var InventoryTransactionPacket $packet */
        if (($packet = $event->getPacket()) instanceof InventoryTransactionPacket) {
            $event->setCancelled($this->handleInventoryTransaction($packet, $event->getPlayer()));
        }
    }

    /**
     * Don't expect much from this handler. Most of it is roughly hacked and duct-taped together.
     *
     * @param InventoryTransactionPacket $packet
     * @param Player $player
     * @return bool
     */
    public function handleInventoryTransaction(InventoryTransactionPacket $packet, Player $player): bool
    {
        switch ($packet->transactionType) {
            case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY:
                {
                    $type = $packet->trData->actionType;
                    switch ($type) {
                        case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT:
                        case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK:
                            {
                                if (array_key_exists($packet->trData->entityRuntimeId, Loader::$particles)) {
                                    if (array_key_exists($player->getName(), Loader::$removing)) {
                                        Loader::$particles[$packet->trData->entityRuntimeId]->remove();
                                        unset(Loader::$particles[$packet->trData->entityRuntimeId]);
                                        unset(Loader::$removing[$player->getName()]);
                                    } else if (array_key_exists($player->getName(), Loader::$editing)) {
                                        Loader::$editing[$player->getName()] = $packet->trData->entityRuntimeId;
                                        $book = new WritableBook();
                                        $book->setCustomName(Loader::$particles[$packet->trData->entityRuntimeId]->getTitle());
                                        $title = Loader::$particles[$packet->trData->entityRuntimeId]->getTitle();
                                        $text = Loader::$particles[$packet->trData->entityRuntimeId]->getText();
                                        if (!empty(trim($text))) $title .= TextFormat::EOL . TextFormat::RESET . $text;
                                        $book->setPageText(0, $title);
                                        $player->getInventory()->addItem($book);
                                        $player->sendMessage(TextFormat::GREEN . "Please modify the text of the book to edit the particle. First line is the title.");
                                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new EditHighlightTask(Loader::$editing[$player->getName()], $player->getName()), 20);
                                    }
                                }
                            }
                    }
                    break;
                }
        }
        return false;
    }

    public function onBookEdit(PlayerEditBookEvent $event)
    {
        $player = $event->getPlayer();
        if ($event->getAction() !== PlayerEditBookEvent::ACTION_REPLACE_PAGE) {
            $event->setCancelled();
            $player->sendMessage(TextFormat::RED . "Please only MODIFY the text, do NOT add or delete pages!");
            return;
        }
        $text = explode(TextFormat::EOL, $event->getNewBook()->getPageText(0));
        $title = array_shift($text);
        if (is_null($title)) {
            $event->setCancelled();
            $player->sendMessage(TextFormat::RED . "Title can not be empty!");
            return;
        }
        Loader::$particles[Loader::$editing[$player->getName()]]->setTitle($title);
        Loader::$particles[Loader::$editing[$player->getName()]]->setText(implode(TextFormat::EOL, $text));
        $player->sendMessage(TextFormat::GREEN . "Text successfully changed");
        $player->sendMessage(TextFormat::GREEN . "Drop book to stop editing");
    }

    public function onDrop(PlayerDropItemEvent $event)
    {
        if (!$event->getItem() instanceof WritableBook) return;
        $player = $event->getPlayer();
        if (isset(Loader::$editing[$player->getName()])) {
            $id = Loader::$editing[$player->getName()];
            unset(Loader::$editing[$player->getName()]);
            $event->setCancelled();
            $player->getInventory()->remove($event->getItem());
            $player->sendMessage(TextFormat::GOLD . "Stopped editing particle $id (was " . $event->getItem()->getCustomName() . ")");
        }
    }

    public function chunkLoad(ChunkLoadEvent $event)
    {//To survive a restart/chunk reload
        foreach (Loader::$particles as $particle) {
            if (($event->getChunk()->getX() * 16) <= $particle->asVector3()->getX() && ($particle->asVector3()->getX() < $event->getChunk()->getX() * 16 + 16))
                if (($event->getChunk()->getZ() * 16) <= $particle->asVector3()->getZ() && ($particle->asVector3()->getZ() < $event->getChunk()->getZ() * 16 + 16)) {
                    $event->getLevel()->addParticle($particle);
                }
        }
    }

    public function playerJoin(PlayerJoinEvent $event)
    {
        foreach (Loader::$particles as $particle) {
            if ($event->getPlayer()->getLevel()->getName() === $particle->getLevel()->getName()) {
                $event->getPlayer()->getLevel()->addParticle($particle, [$event->getPlayer()]);
            }
        }
    }
}