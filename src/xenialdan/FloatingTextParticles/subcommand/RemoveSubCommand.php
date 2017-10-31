<?php

namespace xenialdan\FloatingTextParticles\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\FloatingTextParticles\Loader;

class RemoveSubCommand extends SubCommand{

	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("floatingtextparticles.command.remove");
	}

	public function getUsage(){
		return "remove";
	}

	public function getName(){
		return "remove";
	}

	public function getDescription(){
		return "Remove a particle";
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
		Loader::$removing[$sender->getName()] = true;
		$sender->sendMessage(TextFormat::GREEN . 'Now tap a floating text particle to remove it');
		return true;
	}
}
