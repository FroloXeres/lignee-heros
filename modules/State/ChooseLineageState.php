<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;

class ChooseLineageState extends AbstractState
{
    public const ID = 3;

    public const NAME = 'ChooseLineage';
    public const ACTION_SELECT_LINEAGE = 'selectLineage';
    public const TRANSITION_NEXT = 'next';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate("Everyone have to choose the lineage they will play with.");
        $this->descriptionMyTurn = clienttranslate("Choose lineage you will play with.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [self::ACTION_SELECT_LINEAGE];
        $this->transitions       = [self::TRANSITION_NEXT => DrawObjectiveState::ID];
    }

    public function getActionCleanMethods(\APP_GameAction $gameAction): ?array
    {
        return [
            self::ACTION_SELECT_LINEAGE => function ($args) use ($gameAction) {
                // If all player choose Lineage, next step...
                // Clean args to send to Action method


                // Call game action method
                //$gameAction->game->{self::ACTION_SELECT_LINEAGE}();
            }
        ];
    }


    public function getActionMethods(\Table $game): ?array
    {
        return [
            self::ACTION_SELECT_LINEAGE => function ($args) use ($game) {
                // If all player choose Lineage, next step...

                $game->gamestate->setAllPlayersNonMultiactive(ChooseLineageState::TRANSITION_NEXT);
            }
        ];
    }

    public function getStateArgMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            /** @var Deck $lineageDeck */
            $lineageDeck = $game->cards[AbstractCard::TYPE_LINEAGE];
            $cards = $lineageDeck->getCards();

            return [
                'i18n' => ['lineageChoice'],
                'lineageChoice' => clienttranslate('Please choose 1 lineage among the '.count($cards))
            ];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            $game->gamestate->setAllPlayersMultiactive();
        };
    }
}
