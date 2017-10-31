<?php

namespace xenialdan\FloatingTextParticles;

use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use xenialdan\FloatingTextParticles\other\FakeFloatingTextParticle;

class EventListener implements Listener{
	/** @var Loader */
	public $owner;

	public function __construct(Plugin $plugin){
		$this->owner = $plugin;
	}

	public function onDataPacket(DataPacketReceiveEvent $event){
		if ($event->getPacket() instanceof InventoryTransactionPacket){
			$event->setCancelled($this->handleInventoryTransaction($event->getPacket(), $event->getPlayer()));
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
		foreach (Loader::$particles as $particleid => $particle){
			if (($event->getChunk()->getX() * 16) <= $particle->asVector3()->getX() && ($particle->asVector3()->getX() < $event->getChunk()->getX() * 16 + 16))
				if (($event->getChunk()->getZ() * 16) <= $particle->asVector3()->getZ() && ($particle->asVector3()->getZ() < $event->getChunk()->getZ() * 16 + 16))
					$event->getLevel()->addParticle($particle, $event->getLevel()->getPlayers());
		}
	}

	public function onLevelLoad(LevelLoadEvent $event){
		foreach (Loader::getInstance()->getConfig()->getAll() as $id => $data){
			var_dump($data);
			if ($event->getLevel()->getName() === $data["levelname"]){
				$ftp = new FakeFloatingTextParticle(new Position($data["x"], $data["y"], $data["z"], Server::getInstance()->getLevelByName($data["levelname"])), $data["text"], $data["title"]);
				$ftp->setEntityId($id);
				Loader::$particles[$id] = $ftp;
			}
		}
		var_dump(Loader::$particles);
	}

	public function playerJoin(PlayerJoinEvent $event){
		var_dump(Loader::$particles);
		foreach (Loader::$particles as $particleid => $particle){
			if ($event->getPlayer()->getLevel()->getName() === $particle->getLevel()->getName()){
				$event->getPlayer()->getLevel()->addParticle($particle, [$event->getPlayer()]);
			}
		}
	}
}