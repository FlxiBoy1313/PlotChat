<?php

namespace flxiboy\PlotChat;

use flxiboy\PlotChat\cmd\ChatCommand;
use pocketmine\plugin\PluginBase;
use flxiboy\PlotChat\events\ChatEvent;
use MyPlot\forms\MyPlotForm;
use MyPlot\MyPlot;
use MyPlot\subcommand\SubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;

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
    public function onEnable() {
        self::$instance = $this;
        $config = $this->getConfig();
        $this->reloadConfig();
        $this->getServer()->getPluginManager()->registerEvents(new ChatEvent(), $this);
        if ($config->getNested("settings.cmd.mode") == "myplot") {
            $this->registerSubCommand();
        } elseif ($config->getNested("settings.cmd.mode") == "plugin") {
            $this->getServer()->getCommandMap()->register("PlotChat", new ChatCommand());
        }
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    
    /**
     * Registers the /p chat subcommand
     *
     * @return void
     */
    private function registerSubCommand(): void
    {
        /** @var \MyPlot\Commands $commands */
        $commands = Main::getInstance()->getServer()->getCommandMap()->getCommand('plot');
        if(is_null($commands))
            return;
        if(!version_compare(MyPlot::getInstance()->getDescription()->getVersion(), '1.9.0', '>='))
            return;
        $command = new class(MyPlot::getInstance(), 'chat') extends SubCommand {
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
            public function getAlias(): string
            {
                $config = Main::getInstance()->getConfig();
                return $config->getNested("settings.cmd.aliases");
            }
            public function canUse(CommandSender $sender): bool
            {
                return $sender instanceof Player;
            }
            public function execute(CommandSender $sender, array $args): bool
            {
                Main::getInstance()->getServer()->dispatchCommand($sender, $this->getName() . ' ' . implode(' ', $args));
                return true;
            }
            public function getForm(?Player $player = null): ?MyPlotForm
            {
                return null;
            }
        };
        $commands->loadSubCommand($command);
    }
}
