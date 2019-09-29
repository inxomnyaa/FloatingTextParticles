<?php

declare(strict_types=1);

namespace xenialdan\FloatingTextParticles\other;

use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use xenialdan\FloatingTextParticles\Loader;

class EditHighlightTask extends Task
{
    private $particleId;
    private $playerName;

    /**
     * EditHighlightTask constructor.
     * @param int $particleId
     * @param string $playerName
     */
    public function __construct(int $particleId, string $playerName)
    {
        $this->particleId = $particleId;
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
        if (!isset(Loader::$editing[$this->playerName])) {
            $this->getHandler()->cancel();
            return;
        }
        if (!isset(Loader::$particles[$this->particleId])) {
            $this->getHandler()->cancel();
            return;
        }
        Loader::$particles[$this->particleId]->getLevel()->addParticle(new GenericParticle(Loader::$particles[$this->particleId]->asVector3()->add(0, 0.15), Particle::TYPE_VILLAGER_HAPPY), [$player]);
    }
}