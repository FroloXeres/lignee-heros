<?php

namespace LdH\State;

class PrincipalState extends AbstractState
{
    public const ID = 5;
    public const NAME = 'Principal';

    public const ACTION_END_TURN = 'endTurn';

    public const TR_END_TURN = 'trEndTurn';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_ACTIVE;
        $this->description       = clienttranslate('${actplayer} has to choose an action to do');
        $this->descriptionMyTurn = clienttranslate("Please choose an action to do");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = [
            self::ACTION_END_TURN,
        ];
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            self::TR_END_TURN => DeadEndState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            // Send data for this state


            return [
                'data' => 'Content'
            ];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            // Action start

        };
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_END_TURN => function() {
                /** @var \action_ligneeheros $this */



                $this->game->{PrincipalState::ACTION_END_TURN}();
            }
        ];
    }

    public function getActionMethods(): array
    {
        return [
            self::ACTION_END_TURN => function() {
                /** @var \ligneeheros $this */
                // No more action to do, launch end of turn...



                $this->gamestate->nextState();
            }
        ];
    }
}
