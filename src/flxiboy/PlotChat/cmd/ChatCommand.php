<?php

namespace flxiboy\PlotChat\cmd;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};
use pocketmine\{
    Server,
    Player
};
use pocketmine\utils\Config;
use flxiboy\PlotChat\Main;
use MyPlot\MyPlot;

/**
 * Class ChatCommand
 * @package flxiboy\PlotChat\cmd
 */
class ChatCommand extends PluginCommand
{

    /**
	 * Commands constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) 
    {
        $this->plugin = $plugin;
        $config = new Config($plugin->getDataFolder() . "config.yml", Config::YAML);
        parent::__construct($config->getNested("settings.cmd.command"), $plugin);
		$this->setAliases([$config->getNested("settings.cmd.aliases")]);
		$this->setDescription($config->getNested("settings.cmd.desc"));
		$this->setUsage($config->getNested("settings.cmd.usage"));
    }
    
    /**
	 * @param CommandSender $player
	 * @param string $alias
	 * @param string[] $args
	 */
    public function execute(CommandSender $player, string $alias, array $args) 
    {
        $api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
        $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        if ($config->getNested("settings.chat.enable") == true) {
            if (isset($args[0])) {
                if ($args[0] == $config->getNested("settings.chat.cmd-on")) {
                    $this->plugin->playerchat[] = $player->getName();
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.chat.cmd-on"));
                } elseif ($args[0] == $config->getNested("settings.chat.cmd-off")) {
                    unset($this->plugin->playerchat[array_search($player, $this->plugin->playerchat)]);
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.chat.cmd-off"));
                } else {
                    $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.chat.usage"));
                }
            } else {
                $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.chat.usage"));
            }
        } else {
            if (isset($args[0])) {
                if ($config->getNested("settings.ui.enable") == true and $args[0] == $config->getNested("settings.ui.cmd") and $api) {
                    $form = $api->createCustomForm(function (Player $player, $data = null) { 
                        if ($data === null) {
                            return; 
                        }
                        $this->sendChat($player, $data[0]);
                    });
                    $form->setTitle($config->getNested("message.ui.title"));
                    $form->addInput($config->getNested("message.ui.text"), $config->getNested("message.ui.input"));
                    $form->sendToPlayer($player);
                    return $form;
                } else {
                    $text = implode(" ", $args);
                    $this->sendChat($player, $text);
                }
            } else {
                $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
            }
        }
        return true;
    }

    /**
	 * @param Player $player
	 * @param string $message
	 */
    public function sendChat(Player $player, string $message) {
        $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        $plot = MyPlot::getInstance()->getPlotByPosition($player);
        if (!MyPlot::getInstance()->isLevelLoaded($player->getLevelNonNull()->getFolderName())) {
            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.no-world"));
            return; 
        }
        if ($plot === null) {
            $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.cmd.no-plot"));
            return;
        }
        if (!empty($message)) {
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
                        } elseif ($config->getNested("settings.see-chat.mode") == "false") {
                        } else {
                            Main::getInstance()->getLogger()->warning("please change the settings -> mode. use players, permission or false!");
                        }
                    } 
                }
            }
        } else {
            if ($config->getNested("settings.chat.enable") == true) {
                $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.chat.usage"));
            } else{
                $player->sendMessage($config->getNested("message.prefix") . $config->getNested("settings.cmd.usage"));
            }
        }
    }
}
