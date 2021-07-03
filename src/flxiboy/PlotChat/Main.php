<?php

namespace flxiboy\PlotChat;

use flxiboy\PlotChat\cmd\ChatCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Class Main
 * @package flxiboy\PlotChat
 */
class Main extends PluginBase
{

    /**
     * Enable function: registering Command
     */
    public function onEnable() {
        $this->saveResource("config.yml");
        if ($this->getServer()->getPluginManager()->getPlugin("MyPlot")) {
            $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            if ($config->getNested("settings.cmd.enable") == true) {
                $this->getServer()->getCommandMap()->register("PlotChat", new ChatCommand($this));
            }
        } else {
            $this->getLogger()->warning("Please install MyPlot!");
        }
    }
}