<?php

namespace xenialdan\FloatingTextParticles\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\FloatingTextParticles\Loader;
use xenialdan\FloatingTextParticles\other\FakeFloatingTextParticle;

class CreateSubCommand extends SubCommand{

	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("floatingtextparticles.command.create");
	}

	public function getUsage(){
		return "create <text>";
	}

	public function getName(){
		return "create";
	}

	public function getDescription(){
		return "Create a floating sign";
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
		/** @var Player $sender */
		$ftp = new FakeFloatingTextParticle($sender->asPosition(), "", $text);
		$sender->getLevel()->addParticle($ftp);
		Loader::$particles[$ftp->getEntityId()] = $ftp;
		$sender->sendMessage(TextFormat::GREEN . "Created particle " . $ftp->getEntityId());
		return true;
	}
}
