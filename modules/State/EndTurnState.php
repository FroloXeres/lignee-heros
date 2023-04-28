<?php

namespace LdH\State;

use LdH\Object\FullScreenMessage;
use LdH\Service\CurrentStateService;

class EndTurnState extends AbstractState
{
    public const ID = 7;
    public const NAME = 'EndPrincipal';

    public const TR_END = 'trEnd';
    public const TR_SCIENCE_HARVEST = 'trNexTurn';

    public const NOTIFY_END_TURN = 'ntfyEndTurn';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Start harvest phase");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            self::TR_SCIENCE_HARVEST => ScienceHarvestBonusState::ID,
            self::TR_END => DeadEndState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $turn = $this->getCurrentTurn();
            $args = [
                'turn' => $turn,
                'endOfTurn' => clienttranslate('End of turn '.$turn),
            ];
            return $args;
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $turn = $this->getCurrentTurn();

            if ($turn < CurrentStateService::LAST_TURN) {
                $this->notifyAllPlayers(EndTurnState::NOTIFY_END_TURN, clienttranslate('[end_turn] phase'), [
                    'fullscreen' => new FullScreenMessage('End phase begin', 0)
                ]);

                $this->gamestate->nextState(EndTurnState::TR_SCIENCE_HARVEST);
                return ;
            }

            if ($this->getCurrentTurn() === CurrentStateService::LAST_TURN) {
                // todo: Drakon fight
            }

            // Last turn
            $this->gamestate->nextState(EndTurnState::TR_END);
        };
    }

    public static function endTurn(\ligneeheros $game): void
    {
        // Activate people ???
        // todo: On 2d objective complete, player became leader : $game->setGameStateValue(CurrentStateService::GLB_LEADER, 4);

        // Inventions
        // Invention activated finished (Change bonuses)
    }
}
