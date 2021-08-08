<?php

namespace flxiboy\PlotChat\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use flxiboy\PlotChat\Main;
use MyPlot\forms\MyPlotForm;
use MyPlot\MyPlot;
use MyPlot\subcommand\SubCommand;
use flxiboy\PlotChat\forms\ChatForm;
use flxiboy\PlotChat\api\ChatAPI;

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

    public function getUsage(): string
    {
        $config = Main::getInstance()->getConfig();
        return $config->getNested("settings.cmd.usage");
    }

    public function getName(): string
    {
        $config = Main::getInstance()->getConfig();
        return $config->getNested("settings.cmd.command");
    }

    public function getDescription(): string
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
     * @return bool
	 */
    public function execute(CommandSender $player, array $args): bool
    {
        if ($player instanceof Player) {
            $config = Main::getInstance()->getConfig();
            $plot = MyPlot::getInstance()->getPlotByPosition($player);
            $form= new ChatForm();
            $api = new ChatAPI();
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
                            $api->sendChat($player, $text);
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
                    } elseif ($args[0] == $config->getNested("settings.log.cmd")) {
                        if ($args[0] == $config->getNested("settings.log.cmd")) {
                            $form->getChatLog($player, $plot->X, $plot->Z);
                        } else {
                            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
                        }
                    } else {
                        if ($config->getNested("settings.ui.enable") == true and $args[0] == $config->getNested("settings.ui.cmd")) {
                            $form->sendChatUI($player);
                        } else {
                            $text = implode(" ", $args);
                            if ($config->getNested("settings.chat.color-chat") == false and strpos($text, "§") !== false) {
                                foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                                    $text = str_replace("§" . $colors, "", $text);
                                }
                            }
                            $api->sendChat($player, $text);
                        }
                    }
                } else {
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
                }
            }
        }
        return true;
    }
}