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
	 * Listener constructor.
	 *
	 * @param Main $plugin
	 */
    public function __construct(Main $plugin) 
    {
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if (in_array($player->getName(), $this->plugin->playerchat) and MyPlot::getInstance()->isLevelLoaded($player->getLevelNonNull()->getFolderName())) {
            $event->setCancelled();
            if ($message !== null) {
                $chat = new ChatCommand($this->plugin, $player);
                $chat->sendChat($player, $message);
            }
        }
    }
}
