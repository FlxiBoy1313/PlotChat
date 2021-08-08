<?php

namespace flxiboy\PlotChat\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use flxiboy\PlotChat\api\ChatAPI;
use pocketmine\Player;
use flxiboy\PlotChat\Main;

/**
 * Class ChatForm
 * @package flxiboy\PlotChat\forms
 */
class ChatForm 
{

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function sendChatUI(Player $player): CustomForm
    {
        $config = Main::getInstance()->getConfig();
        $form = new CustomForm(function (Player $player, $data = null) use ($config) {
            if ($data === null) {
                return true;
            }

            $text = $data[0];
            if ($text !== null) {
                if ($config->getNested("settings.chat.color-chat") == false and strpos($text, "§") !== false) {
                    foreach ($config->getNested("settings.chat.color-chat-block") as $colors) {
                        $text = str_replace("§" . $colors, "", $text);
                    }
                }
                $chat = new ChatAPI();
                $chat->sendChat($player, $text);
            } else {
                $player->sendMessage($config->getNested("message.prefix") . $config->getNested("message.chat.not-message"));
            }
            return true;
        });
        $form->setTitle($config->getNested("message.chat.title"));
        $form->addInput($config->getNested("message.chat.text"), $config->getNested("message.chat.input"));
        $player->sendForm($form);
        return $form;
    }

    /**
     * @param Player $player
     * @param string $plotx
     * @param string $plotz
     * @return SimpleForm
     */
    public function getChatLog(Player $player, string $plotx, string $plotz): SimpleForm 
    {
        $config = Main::getInstance()->getConfig();
        $log = Main::getInstance()->getLog();
        $form = new SimpleForm(function (Player $player, $data = null) use ($config, $log, $plotx, $plotz) {
            if ($data === null) {
                return true;
            }

            switch ($data) {
                case "delete":
                    $msg = $config->getNested("message.log.admin-success");
                    $msg = str_replace("%x%", $plotx, $msg);
                    $msg = str_replace("%z%", $plotz, $msg);
                    $player->sendMessage($config->getNested("message.prefix") . $msg);
                    $log->remove($plotx . ";" . $plotz);
                    $log->save();
                    break;
            }
            return true;
        });
        $form->setTitle($config->getNested("message.log.title"));
        if ($log->exists($plotx . ";" . $plotz)) {
            $list = [];
            foreach ($log->get($plotx . ";" . $plotz) as $plot) {
                $plots = explode(":", $plot);
                $format = $config->getNested("message.log.format");
                $format = str_replace("%user%", $plots[0], $format);
                $format = str_replace("%message%", $plots[1], $format);
                $list[] = $format;
            }
            $content = $config->getNested("message.log.text");
            $content = str_replace("%x%", $plotx, $content);
            $content = str_replace("%z%", $plotz, $content);
            $form->setContent($content . "\n§r\n" . implode("\n", $list) . "§r\n§e");
            if ($config->getNested("settings.log.admin-img") !== false and strpos($config->getNested("settings.log.admin-img"), "textures/") !== false) { $picture = 0; } else { $picture = 1; }
            if ($config->getNested("settings.log.admin-perms") !== false) {
                if ($player->hasPermission($config->getNested("settings.log.admin-perms"))) {
                    $form->addButton($config->getNested("message.log.admin-delete"), $picture, $config->getNested("settings.log.admin-img"), "delete");
                }
            } else {
                $form->addButton($config->getNested("message.log.admin-delete"), $picture, $config->getNested("settings.log.admin-img"), "delete");
            }
        } else {
            $form->setContent($config->getNested("message.log.not-inlog"));
        }
        if ($config->getNested("settings.log.okay-img") !== false and strpos($config->getNested("settings.log.okay-img"), "textures/") !== false) { $picture = 0; } else { $picture = 1; }
        $form->addButton($config->getNested("message.log.okay"), $picture, $config->getNested("settings.log.okay-img"), "okay");
        $player->sendForm($form);
        return $form;
    }
}