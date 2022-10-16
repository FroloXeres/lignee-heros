<?php

namespace LdH\State;

class MultiChooseLineageState extends AbstractState
{
    public const ID = 3;

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = 'MultiChooseLineage';
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->initialPrivate    = ChooseLineageState::ID;
        $this->description       = clienttranslate("Choose lineage you will play with.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [];
        $this->transitions       = ["lineageChosenForAll" => DrawObjectiveState::ID];
    }

    public function getActionMethods(\APP_GameAction $gameAction): ?array
    {
        return [
            $this->name => function () use ($gameAction) {}
        ];
    }

    public function getStateArgMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            return [];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            $game::checkAction($this->getName());

            $game->gamestate->setAllPlayersMultiactive();
            $game->gamestate->initializePrivateStateForAllActivePlayers();
        };
    }
}
