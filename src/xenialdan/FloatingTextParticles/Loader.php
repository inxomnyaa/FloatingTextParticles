<?php

namespace xenialdan\FloatingTextParticles;

use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use xenialdan\FloatingTextParticles\other\FakeFloatingTextParticle;

class Loader extends PluginBase
{
    /** @var Loader */
    private static $instance = null;
    /** @var array */
    public static $editing = [];
    /** @var array */
    public static $removing = [];
    /** @var FakeFloatingTextParticle[] */
    public static $particles = [];

    private static function resetConfig()
    {
        foreach (self::getInstance()->getConfig()->getAll(true) as $key) {
            self::getInstance()->getConfig()->remove($key);
        }
    }

    public function onLoad()
    {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->getConfig()->reload();
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register(Commands::class, new Commands($this));
        $this->removeAllParticlesInWorlds();
        foreach (Loader::getInstance()->getConfig()->getAll() as $data) {
            $ftp = new FakeFloatingTextParticle(new Position($data["x"], $data["y"], $data["z"], Server::getInstance()->getLevelByName($data["levelname"])), $data["text"], $data["title"]);
            Loader::$particles[$ftp->getEntityId()] = $ftp;
            Server::getInstance()->getLevelByName($data['levelname'])->addParticle($ftp, Server::getInstance()->getLevelByName($data['levelname'])->getPlayers());
        }
    }

    public function onDisable()
    {
        self::resetConfig();
        $array = [];
        foreach (self::$particles as $particle) {
            $array[] = ["x" => $particle->getX(), "y" => $particle->getY(), "z" => $particle->getZ(), "levelname" => $particle->getLevel()->getName(), "title" => $particle->getTitle(), "text" => $particle->getText()];
        }
        $this->getConfig()->setAll($array);
        $this->getConfig()->save();
        $this->removeAllParticlesInWorlds();
    }

    private function removeAllParticlesInWorlds(): void
    {
        foreach (self::$particles as $particle) {
            $particle->remove();
        }
    }

    /**
     * Returns an instance of the plugin
     * @return Loader
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}