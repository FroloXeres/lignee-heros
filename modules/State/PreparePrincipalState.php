<?php

namespace LdH\State;

class PreparePrincipalState extends AbstractState
{
    public const ID = 6;
    public const NAME = 'PreparePrincipal';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Turn is about to start");
        $this->descriptionMyTurn = clienttranslate("Turn is about to start");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = [];
        $this->transitions       = [
            "" => PrincipalState::ID,
        ];
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $this->gamestate->setAllPlayersMultiactive();
            $this->gamestate->nextState();
        };
    }
}
