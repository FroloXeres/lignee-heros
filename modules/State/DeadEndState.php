<?php

namespace LdH\State;

class DeadEndState extends AbstractState
{
    public const ID = 98;
    public const NAME = 'DeadEnd';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_ACTIVE;
        $this->description       = clienttranslate("Everyone is waiting...");
        $this->descriptionMyTurn = clienttranslate("Nothing to do but wait");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = ['wait'];
        $this->transitions       = ["" => StateInterface::STATE_END_ID];
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            // Do nothing
        };
    }
}
