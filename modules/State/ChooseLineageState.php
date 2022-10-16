<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;

class ChooseLineageState extends AbstractState
{
    public const ID = 4;

    public const ACTION_SELECT_LINEAGE = 'SelectLineage';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = 'ChooseLineage';
        $this->type              = self::TYPE_PRIVATE;
        $this->description       = clienttranslate("Choose lineage you will play with.");
        $this->descriptionMyTurn = clienttranslate("Everyone have to choose the lineage they will play with.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [self::ACTION_SELECT_LINEAGE];
        $this->transitions       = [
            "" => DrawObjectiveState::ID
        ];
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
                'lineage' => $game->cards[AbstractCard::TYPE_LINEAGE],
            ];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            $game::checkAction($this->getName());

            // User enter this state and will choose Lineage

        };
    }
}
