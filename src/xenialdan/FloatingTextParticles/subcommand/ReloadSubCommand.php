<?php

namespace xenialdan\FloatingTextParticles\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xenialdan\FloatingTextParticles\Loader;
use xenialdan\FloatingTextParticles\other\FakeFloatingTextParticle;

class ReloadSubCommand extends SubCommand{

	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("floatingtextparticles.command.reload");
	}

	public function getUsage(){
		return "reload";
	}

	public function getName(){
		return "reload";
	}

	public function getDescription(){
		return "Reload existing particles";
	}

	public function getAliases(){
		return [];
	}

	/**
	 * @param CommandSender $sender
	 * @param array $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args){
		/**
		 * @var Player $sender
		 * @var $particleid
		 * @var FakeFloatingTextParticle $particle
		 */
		foreach (Loader::$particles as $particleid => $particle){
			$particle->getLevel()->addParticle($particle, $particle->getLevel()->getPlayers());
		}
		return true;
	}
}
