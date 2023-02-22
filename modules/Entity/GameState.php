<?php

namespace LdH\Entity;

use LdH\Service\GameStateService;

class GameState
{
    public int $turnLeft = GameStateService::DEFAULT_MAX_TURN;

    /**
     * @param int $maxTurn
     */
    public function __construct(int $maxTurn)
    {
        $this->turnLeft = $maxTurn;
    }
}
