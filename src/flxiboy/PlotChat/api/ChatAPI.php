<?php

namespace flxiboy\PlotChat\api;

use flxiboy\PlotChat\Main;
use MyPlot\MyPlot;
use pocketmine\Server;
use pocketmine\Player;

/**
 * Class ChatAPI
 * @package flxiboy\PlotChat\api
 */
class ChatAPI
{

    /**
	 * @param Player $player
	 * @param string $message
	 */
    public function sendChat(Player $player, string $message): bool
    {
        $config = Main::getInstance()->getConfig();
        $log = Main::getInstance()->getLog();
        $plot = MyPlot::getInstance()->getPlotByPosition($player);
        $date = new \DateTime('now');
        if ($plot !== null) {
            if (!empty($message)) {
                $logsave = $log->getNested($player->getLevel()->getFolderName() . $plot->X . ";" . $plot->Z);
                $logsave[] = $date->format("Y:m:d:H:i:s") . ":" . $player->getName() . ":" . $message;
                $log->setNested($player->getLevel()->getFolderName() . "." . $plot->X . ";" . $plot->Z, $logsave);
                $log->save();
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