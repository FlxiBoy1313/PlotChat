<?php

namespace flxiboy\PlotChat\events;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use flxiboy\PlotChat\Main;
use flxiboy\PlotChat\cmd\ChatCommand;
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
        if (in_array($player->getName(), Main::getInstance()->playerchat) and MyPlot::getInstance()->isLevelLoaded($player->getLevelNonNull()->getFolderName())) {
            $event->setCancelled();
            if ($message !== null) {
                $chat = new ChatCommand();
                $chat->sendChat($player, $message);
            }
        }
    }
}
