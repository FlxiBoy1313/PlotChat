<?php

namespace flxiboy\PlotChat;

use flxiboy\PlotChat\cmd\ChatCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use flxiboy\PlotChat\events\ChatEvent;

/**
 * Class Main
 * @package flxiboy\PlotChat
 */
class Main extends PluginBase
{

    /**
     * @var array
     */
    public $playerchat = [];

    /**
     * Enable function: registering Command
     */
    public function onEnable() {
        $this->saveResource("config.yml");
        $this->getServer()->getPluginManager()->registerEvents(new ChatEvent($this), $this);
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($config->getNested("settings.cmd.enable") == true) {
            $this->getServer()->getCommandMap()->register("PlotChat", new ChatCommand($this));
        }
    }
}