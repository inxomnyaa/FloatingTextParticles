<?php

namespace xenialdan\FloatingTextParticles;

use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class EventListener implements Listener{
	/** @var Loader */
	public $owner;

	public function __construct(Plugin $plugin){
		$this->owner = $plugin;
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event){
		/** @var InventoryTransactionPacket $packet */
		if (($packet = $event->getPacket()) instanceof InventoryTransactionPacket){
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
	public function handleInventoryTransaction(InventoryTransactionPacket $packet, Player $player): bool{
		switch ($packet->transactionType){
			case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY: {
				$type = $packet->trData->actionType;
				switch ($type){
					case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT:
					case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK: {
						if (array_key_exists($packet->trData->entityRuntimeId, Loader::$particles)){
							if (array_key_exists($player->getName(), Loader::$removing)){
								Loader::$particles[$packet->trData->entityRuntimeId]->remove();
								unset(Loader::$particles[$packet->trData->entityRuntimeId]);
								unset(Loader::$removing[$player->getName()]);
							} elseif (array_key_exists($player->getName(), Loader::$editing)){
								Loader::$particles[$packet->trData->entityRuntimeId]->setTitle(Loader::$editing[$player->getName()]);
								unset(Loader::$editing[$player->getName()]);
							}
						}
					}
				}
				break;
			}
		}
		return false;
	}

	public function chunkLoad(ChunkLoadEvent $event){//To survive a restart/chunk reload
		foreach (Loader::$particles as $particle){
			if (($event->getChunk()->getX() * 16) <= $particle->asVector3()->getX() && ($particle->asVector3()->getX() < $event->getChunk()->getX() * 16 + 16))
				if (($event->getChunk()->getZ() * 16) <= $particle->asVector3()->getZ() && ($particle->asVector3()->getZ() < $event->getChunk()->getZ() * 16 + 16)){
					$event->getLevel()->addParticle($particle);
				}
		}
	}

	public function playerJoin(PlayerJoinEvent $event){
		foreach (Loader::$particles as $particle){
			if ($event->getPlayer()->getLevel()->getName() === $particle->getLevel()->getName()){
				$event->getPlayer()->getLevel()->addParticle($particle, [$event->getPlayer()]);
			}
		}
	}
}