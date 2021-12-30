<?php

namespace jojoe77777\FormAPI;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

/**
 * Class Form
 * @package jojoe77777\FormAPI
 */
abstract class Form implements IForm
{
    /**
     * @var array
     */
    protected array $data = [];
    /**
     * @var callable|null
     */
    private $callable;

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @return callable|null
     */
    public function getCallable(): ?callable
    {
        return $this->callable;
    }

    /**
     * @param callable|null $callable
     */
    public function setCallable(?callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param Player $player
     * @param mixed $data
     */
    public function handleResponse(Player $player, $data): void
    {
        $this->processData($data);
        $callable = $this->getCallable();
        if($callable !== null) {
            $callable($player, $data);
        }
    }

    /**
     * @param $data
     */
    public function processData(&$data): void {}

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}