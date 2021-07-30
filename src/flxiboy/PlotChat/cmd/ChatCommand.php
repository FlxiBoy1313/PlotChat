<?php

namespace flxiboy\PlotChat\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\Player;
use flxiboy\PlotChat\Main;
use MyPlot\forms\MyPlotForm;
use MyPlot\MyPlot;
use MyPlot\subcommand\SubCommand;
use jojoe77777\FormAPI\CustomForm;

/**
 * Class ChatCommand
 * @package flxiboy\PlotChat\cmd
 */
class ChatCommand extends SubCommand
{

	public function __construct() 
    {
        parent::__construct(MyPlot::getInstance(), $this->getName());
    }

    public function getUsage() : string 
    {
        $config = Main::getInstance()->getConfig();
        return $config->getNested("settings.cmd.usage");
    }

    public function getName(): string
    {
        $config = Main::getInstance()->getConfig();
        return $config->getNested("settings.cmd.command");
    }

    public function getDescription() : string 
    {
        $config = Main::getInstance()->getConfig();
        return $config->getNested("settings.cmd.desc");
    }

    /**
	 * @param CommandSender $player
	 */
    public function canUse(CommandSender $player): bool
    {
        return $player instanceof Player;
    }

    /**
     * @param Player $player
     */
	public function getForm(?Player $player = null) : ?MyPlotForm 
    {
		return null;
	}
    
    /**
	 * @param CommandSender $player
	 * @param string[] $args
	 */
    public function execute(CommandSender $player, array $args): bool
    {
        if ($player instanceof Player) {
            $config = Main::getInstance()->getConfig();
            $plot = MyPlot::getInstance()->getPlotByPosition($player);
            if ($config->getNested("settings.world.enable") == true) {
                foreach ($config->getNested("settings.world.worlds") as $worlds) {
                    if (MyPlot::getInstance()->isLevelLoaded($worlds)) {
                        if (isset($args[0])) {
                            $text = implode(" ", $args);
                            if ($config->getNested("settings.chat.color-chat") == false and strpos($text, "§") !== false) {
                                foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                                    $text = str_replace("§" . $colors, "", $text);
                                }
                            }
                            $this->sendChat($player, $text);
                        } else {
                            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
                        }
                    }
                }
            } else {
                if (!MyPlot::getInstance()->isLevelLoaded($player->getLevelNonNull()->getFolderName())) {
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.no-world"));
                    return true;
                }
                if ($plot === null) {
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.no-plot"));
                    return true;
                }
                if (isset($args[0])) {
                    if ($args[0] == $config->getNested("settings.chat.cmd-on") or $args[0] == $config->getNested("settings.chat.cmd-off")) {
                        if ($args[0] == $config->getNested("settings.chat.cmd-on")) {
                            Main::getInstance()->playerchat[] = $player->getName();
                            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.chat.cmd-on"));
                        } elseif ($args[0] == $config->getNested("settings.chat.cmd-off")) {
                            unset(Main::getInstance()->playerchat[array_search($player, Main::getInstance()->playerchat)]);
                            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.chat.cmd-off"));
                        } else {
                            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
                        }
                    } else {
                        if ($config->getNested("settings.ui.enable") == true and $args[0] == $config->getNested("settings.ui.cmd")) {
                            $this->sendUI($player);
                        } else {
                            $text = implode(" ", $args);
                            if ($config->getNested("settings.chat.color-chat") == false and strpos($text, "§") !== false) {
                                foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                                    $text = str_replace("§" . $colors, "", $text);
                                }
                            }
                            $this->sendChat($player, $text);
                        }
                    }
                } else {
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
                }
            }
        }
        return true;
    }

    /**
	 * @param Player $player
	 */
    public function sendUI(Player $player): CustomForm
    {
        $config = Main::getInstance()->getConfig();
        $form = new CustomForm(function (Player $player, $data = null) use ($config) {
            if ($data === null) {
                return;
            }
            $text = $data[0];
            if ($config->getNested("settings.chat.color-chat") == false and strpos($text, "§") !== false) {
                foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                    $text = str_replace("§" . $colors, "", $text);
                }
            }
            $this->sendChat($player, $text);
            return true;
        });
        $form->setTitle($config->getNested("message.ui.title"));
        $form->addInput($config->getNested("message.ui.text"), $config->getNested("message.ui.input"));
        $player->sendForm($form);
        return $form;
    }

    /**
	 * @param Player $player
	 * @param string $message
	 */
    public function sendChat(Player $player, string $message): bool
    {
        $config = Main::getInstance()->getConfig();
        $plot = MyPlot::getInstance()->getPlotByPosition($player);
        if ($plot !== null) {
            if (!empty($message)) {
                foreach (Server::getInstance()->getOnlinePlayers() as $players) {
                    $plotx = MyPlot::getInstance()->getPlotByPosition($players);
                    $msg = $config->getNested("message.cmd.chat-msg");
                    $msg = str_replace("%x%", $plot->X, $msg);
                    $msg = str_replace("%z%", $plot->Z, $msg);
                    $msg = str_replace("%player%", $player->getName(), $msg);
                    $msg = str_replace("%msg%", $message, $msg);
                    if ($plotx !== null and $plotx->X == $plot->X and $plotx->Z == $plot->Z) {
                        if ($players == $player->getName()) {
                            $player->sendMessage($msg);
                        } else{
                            $players->sendMessage($msg);
                        }
                    } else {
                        if ($config->getNested("settings.see-chat.mode") !== false) {
                            if ($config->getNested("settings.see-chat.mode") == "players") {
                                foreach ($config->getNested("settings.see-chat.mode-players") as $chatp) {
                                    if ($chatp instanceof Player) {
                                        if ($chatp->hasPermission($config->getNested("settings.see-chat.mode-perms"))) {
                                            $chatp->sendMessage($config->getNested("settings.see-chat.msg") . $msg);
                                        }
                                    }
                                }
                            } elseif ($config->getNested("settings.see-chat.mode") == "permission") {
                                if ($players->hasPermission($config->getNested("settings.see-chat.mode-perms"))) {
                                    $players->sendMessage($config->getNested("settings.see-chat.msg") . $msg);
                                }
                            }
                        } 
                    }
                }
            } else {
                $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
            }
        } else {
            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.no-plot"));
        }
        return true;
    }
}
