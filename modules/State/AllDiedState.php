<?php

namespace LdH\State;

class AllDiedState extends AbstractState
{
    public const ID = 97;
    public const NAME = 'AllDied';

    public const NOTIFY_ALL_DIED = 'ntfyAllDied';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Everybody died");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = [];
        $this->transitions       = ["" => StateInterface::STATE_END_ID];
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */

            $this->notifyAllPlayers(AllDiedState::NOTIFY_ALL_DIED, clienttranslate('Finally, everybody died! Remember that Estiny is a dangerous place...'), [
                'fullscreen' => clienttranslate('Game Over')
            ]);

            $this->gamestate->nextState();
        };
    }
}
