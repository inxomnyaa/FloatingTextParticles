<?php

namespace xenialdan\FloatingTextParticles\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\FloatingTextParticles\Loader;

class EditSubCommand extends SubCommand{

	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("floatingtextparticles.command.edit");
	}

	public function getUsage(){
		return "edit";
	}

	public function getName(){
		return "edit";
	}

	public function getDescription(){
		return "Tap a particle after this command to edit it";
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
		if (empty($args)) return false;
		if (empty(($text = trim(implode(" ", $args))))) return false;
		Loader::$editing[$sender->getName()] = $text;
		$sender->sendMessage(TextFormat::GREEN . 'Now tap a floating text particle to edit it');
		return true;
	}
}
