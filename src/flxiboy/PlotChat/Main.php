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
     * @var Main
     */
    public static Main $instance;
    /**
     * @var array
     */
    public array $playerchat = [];

    /**
     * Enable function: registering Command
     */
    public function onEnable(): void
    {
        self::$instance = $this;
        $this->reloadConfig();

        # register event
        $this->getServer()->getPluginManager()->registerEvents(new ChatEvent(), $this);

        # register command
        $cmd = $this->getServer()->getCommandMap()->getCommand("plot");
        $cmd->loadSubCommand(new ChatCommand());
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    /**
     * @return Config
     */
    public function getLog(): Config
    {
        return new Config(Main::getInstance()->getDataFolder() . "log.yml", Config::YAML);
    }
}
