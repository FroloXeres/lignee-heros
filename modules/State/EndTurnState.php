<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\PeopleService;
use LdH\Service\CurrentStateService;

class EndTurnState extends AbstractState
{
    public const ID = 7;
    public const NAME = 'EndTurn';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("End of turn...");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = ["" => DeadEndState::ID];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            // Available actions for this turn
            $args = [];
            $this->addPlayersInfosForArgs($args);

            return $args;
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */



            $this->gamestate->nextState();
        };
    }
}
