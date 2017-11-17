<?php

namespace xenialdan\FloatingTextParticles;

use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use xenialdan\FloatingTextParticles\other\FakeFloatingTextParticle;


class Loader extends PluginBase{
	/** @var Loader */
	private static $instance = null;
	/** @var array */
	public static $editing = [];
	/** @var array */
	public static $removing = [];
	/** @var FakeFloatingTextParticle[] */
	public static $particles = [];

	private static function resetConfig(){
		foreach (self::getInstance()->getConfig()->getAll(true) as $key){
			self::getInstance()->getConfig()->remove($key);
		}
	}

	public function onLoad(){
		self::$instance = $this;
		$this->saveDefaultConfig();
		$this->getConfig()->reload();
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getCommandMap()->register(Commands::class, new Commands($this));
		foreach (Loader::getInstance()->getConfig()->getAll() as $id => $data){
			$ftp = new FakeFloatingTextParticle(new Position($data["x"], $data["y"], $data["z"], Server::getInstance()->getLevelByName($data["levelname"])), $data["text"], $data["title"]);
			$ftp->setEntityId($id);
			Loader::$particles[$ftp->getEntityId()] = $ftp;
			Server::getInstance()->getLevelByName($data['levelname'])->addParticle($ftp, Server::getInstance()->getLevelByName($data['levelname'])->getPlayers());
		}
	}

	public function onDisable(){
		self::resetConfig();
		foreach (self::$particles as $particleid => $particle){
			$this->getConfig()->set($particleid, ["x" => $particle->getX(), "y" => $particle->getY(), "z" => $particle->getZ(), "levelname" => $particle->getLevel()->getName(), "title" => $particle->getTitle(), "text" => $particle->getText()]);
		}
		$this->getConfig()->save();
	}

	/**
	 * Returns an instance of the plugin
	 * @return Loader
	 */
	public static function getInstance(){
		return self::$instance;
	}
}