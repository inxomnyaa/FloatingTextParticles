<?php

declare(strict_types=1);

namespace xenialdan\FloatingTextParticles\other;

use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use xenialdan\FloatingTextParticles\Loader;

class EditHighlightAllTask extends Task
{
    private $playerName;

    /**
     * EditHighlightAllTask constructor.
     * @param string $playerName
     */
    public function __construct(string $playerName)
    {
        $this->playerName = $playerName;
    }

    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        if (!($player = Loader::getInstance()->getServer()->getPlayerExact($this->playerName)) instanceof Player) {
            $this->getHandler()->cancel();
            return;
        }
        if (!isset(Loader::$editing[$this->playerName]) && !isset(Loader::$removing[$this->playerName])) {
            $this->getHandler()->cancel();
            return;
        }
        if (isset(Loader::$editing[$this->playerName]) && Loader::$editing[$this->playerName] !== true) {
            $this->getHandler()->cancel();
            return;
        }
        foreach (Loader::$particles as $particle) {
            if ($particle->getLevel() !== $player->getLevel()) continue;
            $player->getLevel()->addParticle(new GenericParticle($particle->asVector3(), Particle::TYPE_FLAME), [$player]);
        }
    }
}