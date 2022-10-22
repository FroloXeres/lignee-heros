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
        $this->transitions       = ["" => StateInterface::STATE_END_ID];
    }

    public function getActionMethods(\Table $game): ?array
    {
        return [];
    }

    public function getStateArgMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            // Send objectives info
            return [
                'data' => 'Content'
            ];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            $game->checkAction(self::NAME);

            // Draw on objective by player (notify)

            $game->gamestate->nextState("");
        };
    }
}
