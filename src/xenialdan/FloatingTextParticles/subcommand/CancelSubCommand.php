<?php

namespace xenialdan\FloatingTextParticles\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\FloatingTextParticles\Loader;

class CancelSubCommand extends SubCommand{

	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("floatingtextparticles.command.cancel");
	}

	public function getUsage(){
		return "cancel";
	}

	public function getName(){
		return "cancel";
	}

	public function getDescription(){
		return "Cancel editing particles";
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
		unset(Loader::$editing[$sender->getName()]);
		unset(Loader::$removing[$sender->getName()]);
		$sender->sendMessage(TextFormat::RED . 'Editing / removing signs was cancelled');
		return true;
	}
}
