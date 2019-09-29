<?php

namespace xenialdan\FloatingTextParticles\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\FloatingTextParticles\Loader;
use xenialdan\FloatingTextParticles\other\EditHighlightAllTask;

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
        Loader::$editing[$sender->getName()] = true;
		$sender->sendMessage(TextFormat::GREEN . 'Now tap a floating text particle to edit it');
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new EditHighlightAllTask($sender->getName()), 20);
        return true;
	}
}
