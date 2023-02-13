<?php

namespace LdH\State;

class DrawObjectiveState extends AbstractState
{
    public const ID = 4;
    public const NAME = 'DrawObjective';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Game is drawing an objective for you.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = ["" => DeadEndState::ID];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            // Send objectives info
            return [
                'data' => 'Content'
            ];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            // Draw an objective by player (notify)



            $this->gamestate->nextState("");
        };
    }
}
