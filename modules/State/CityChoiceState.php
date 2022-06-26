<?php

namespace LdH\State;

class CityChoiceState extends AbstractState
{
    public const ID = 2;

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = 'CityChoice';
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate("Choose action to do.");
        $this->descriptionMyTurn = clienttranslate("You can choose action to do.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [''];
        $this->transitions       = ["" => StateInterface::STATE_END_ID];
    }

    public function getActionMethods(\Table $game): ?array
    {
        return [
            'CityChoice' => function () use ($game) {
                // Do something
                $game->dump('CityChoice ActionMethods');

                //$game::checkAction('CityChoice');

                $game->gamestate->nextState(StateInterface::STATE_END_NAME);
            }
        ];
    }

    public function getStateArgMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            return [
                'data' => 'Content'
            ];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            // Do something
            $game->notification();
        };
    }
}
