<?php

namespace LdH\State;

class ChooseLineageState extends AbstractState
{
    public const ID = 3;

    public const ACTION_SELECT_LINEAGE = 'SelectLineage';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = 'ChooseLineage';
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate("Choose lineage you will play with.");
        $this->descriptionMyTurn = clienttranslate("Everyone have to choose the lineage they will play with.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [self::ACTION_SELECT_LINEAGE];
        $this->transitions       = ["" => DrawObjectiveState::ID];
    }

    public function getActionMethods(\APP_GameAction $gameAction): ?array
    {
        return [
            $this->name => function () use ($gameAction) {

            }
        ];
    }

    public function getStateArgMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            // Send lineage cards
            return [
                'data' => 'Content'
            ];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            $game::checkAction($this->getName());

            // Draw 1 objective

            $game->gamestate->nextState("");
        };
    }
}
