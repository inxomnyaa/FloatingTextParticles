<?php

declare(strict_types=1);

namespace xenialdan\FloatingTextParticles\other;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Server;
use pocketmine\utils\UUID;
use xenialdan\FloatingTextParticles\Loader;

class FakeFloatingTextParticle extends FloatingTextParticle{
	private $level;

	/**
	 * @param Position $pos
	 * @param string $text
	 * @param string $title
	 */
	public function __construct(Position $pos, string $text, string $title = ""){
		$this->text = $text;
		$this->title = $title;
		$this->level = $pos->level;
		parent::__construct($pos->asVector3(), $text, $title);
        $pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->getEntityId();
		$pk->metadata = [Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0.3], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0.3]];
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
	}

	public function remove(){
		$this->setInvisible();
        $pk = new RemoveActorPacket();
		$pk->entityUniqueId = $this->getEntityId();
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
		unset(Loader::$particles[$this->getEntityId()]);
	}

	/**
	 * @return mixed
	 */
	public function getEntityId(){
		if ($this->entityId === null){
			$this->entityId = Entity::$entityCount++;
		}
		return $this->entityId;
	}

	/**
	 * @param mixed $entityId
	 */
	public function setEntityId($entityId){
		$this->entityId = $entityId;
	}

	public function setText(string $text): void{
		parent::setText($text);
        $pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->getEntityId();
		$pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")]];
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
	}

	public function setTitle(string $text): void{
		parent::setTitle($text);
        $pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->getEntityId();
		$pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")]];
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
	}

	/**
	 * @return Level
	 */
	public function getLevel(): Level{
		return $this->level;
	}



	public function encode(){
		$p = [];

		$id = $this->getEntityId();
		if($id === null){
			$this->setEntityId($id);
		}else{
            $pk0 = new RemoveActorPacket();
			$pk0->entityUniqueId = $id;

			$p[] = $pk0;
		}

		if(!$this->invisible){
			$pk = new AddPlayerPacket();
			$pk->uuid = $uuid = UUID::fromRandom();
			$pk->username = "";
			$pk->entityRuntimeId = $id;
			$pk->position = $this->asVector3(); //TODO: check offset
			$pk->item = ItemFactory::get(ItemIds::AIR, 0, 0);

			$flags = (
				(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_IMMOBILE)
			);
			$pk->metadata = [
				Entity::DATA_FLAGS =>   [Entity::DATA_TYPE_LONG,   $flags],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")],
				Entity::DATA_SCALE =>   [Entity::DATA_TYPE_FLOAT,  0.01], //zero causes problems on debug builds
				Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0.3], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0.3]
			];

			$p[] = $pk;

			$skinPk = new PlayerSkinPacket();
			$skinPk->uuid = $uuid;
			$skinPk->skin = new Skin("Standard_Custom", str_repeat("\x00", 8192));
			$p[] = $skinPk;
		}

		return $p;
	}
}
