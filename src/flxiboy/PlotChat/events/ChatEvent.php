<?php

namespace flxiboy\PlotChat\events;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use flxiboy\PlotChat\Main;
use flxiboy\PlotChat\api\ChatAPI;
use MyPlot\MyPlot;

/**
 * Class ChatEvent
 * @package flxiboy\PlotChat\events
 */
class ChatEvent implements Listener
{

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $config = Main::getInstance()->getConfig();
        $chat = new ChatAPI();
        if (in_array($player->getName(), Main::getInstance()->playerchat)) {
            if ($config->getNested("settings.world.enable") == true) {
                foreach ($config->getNested("settings.world.worlds") as $worlds) {
                    if (MyPlot::getInstance()->isLevelLoaded($worlds)) {
                        $event->cancel();
                        if (empty($message)) {
                            if ($message !== null) {
                                if ($config->getNested("settings.chat.color-chat") == false and strpos($message, "§") !== false) {
                                    foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                                        $message = str_replace("§" . $colors, "", $message);
                                    }
                                }
                                $chat->sendChat($player, $message);
                            }
                        } else {
                            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
                        }
                    }
                }
            } else {
                $event->cancel();
                if (MyPlot::getInstance()->isLevelLoaded($player->getWorld()->getFolderName())) {
                    if ($message !== null) {
                        if ($config->getNested("settings.chat.color-chat") == false and strpos($message, "§") !== false) {
                            foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                                $message = str_replace("§" . $colors, "", $message);
                            }
                        }
                        $chat->sendChat($player, $message);
                    }
                } else {
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.no-world"));
                }
            }
        }
    }
}