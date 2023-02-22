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

class GameStartState extends AbstractState
{
    public const NOTIFY_GAME_START = 'ntGameStart';

    public const ID = 4;
    public const NAME = 'GameStart';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Game is starting, good luke!");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = ["" => PrincipalState::ID];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            // No data to send for this
            return [
                'start' => true
            ];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */

            // Warn every one
            $this->notifyAllPlayers(
                GameStartState::NOTIFY_GAME_START,
                clienttranslate('This is your first turn. You have ' . CurrentStateService::LAST_TURN . ' turns to defeat Drakon, good luke!'),
                []
            );

            $this->gamestate->nextState();
        };
    }
}
