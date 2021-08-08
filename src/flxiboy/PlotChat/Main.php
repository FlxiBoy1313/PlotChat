<?php

namespace flxiboy\PlotChat;

use flxiboy\PlotChat\cmd\ChatCommand;
use pocketmine\plugin\PluginBase;
use flxiboy\PlotChat\events\ChatEvent;
use pocketmine\utils\Config;

/**
 * Class Main
 * @package flxiboy\PlotChat
 */
class Main extends PluginBase
{

    /**
     * @var self
     */
    protected static $instance;
    /**
     * @var array
     */
    public $playerchat = [];

    /**
     * Enable function: registering Command
     */
    public function onEnable()
    {
        self::$instance = $this;
        $this->reloadConfig();
        $this->getServer()->getPluginManager()->registerEvents(new ChatEvent(), $this);
        $this->getServer()->getCommandMap()->getCommand("plot")->loadSubCommand(new ChatCommand());
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @return Config
     */
    public static function getLog(): Config
    {
        return new Config(self::$instance->getDataFolder() . "log.yml", Config::YAML);
    }
}
