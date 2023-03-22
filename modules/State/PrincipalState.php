<?php

namespace LdH\State;

class PrincipalState extends AbstractState
{
    public const ID = 5;
    public const NAME = 'Principal';

    public const ACTION_PASS = 'pTurnPass';
    public const ACTION_RESOURCE_HARVEST = 'resourceHarvest';

    public const TR_PASS = 'trPass';

    public const NOTIFY_PLAYER_PASS = 'ntfyPlayerPass';
    public const NOTIFY_START_TURN = 'ntfyStartTurn';


    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate('Everyone have to choose an action or pass');
        $this->descriptionMyTurn = clienttranslate("Please choose an action: ");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = [
            self::ACTION_PASS,
            self::ACTION_RESOURCE_HARVEST,
        ];
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            self::TR_PASS => EndTurnState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $args = [
                'actions' => PrincipalState::getAvailableActions($this),
            ];
            return $this->addPlayersInfosForArgs($args);
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $turn = $this->getCurrentTurn();
            $this->notifyAllPlayers(PrincipalState::NOTIFY_START_TURN, clienttranslate('[turn] <b>${turn}</b> started'), ['i18n' => ['turn'], 'turn' => $turn]);

            $this->gamestate->setAllPlayersMultiactive();
        };
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \action_ligneeheros $this */
                $this->game->{PrincipalState::ACTION_PASS}();
            },
        ];
    }

    public function getActionMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \ligneeheros $this */
                // No more action to do, launch end of turn...
                $notificationParams = [
                    'i18n' => ['player_name'],
                    'player_name' => $this->getCurrentPlayerName(),
                ];
                $this->notifyAllPlayers(
                    PrincipalState::NOTIFY_PLAYER_PASS,
                    clienttranslate('${player_name} choose to end the turn'),
                    $notificationParams
                );

                $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayerId(), PrincipalState::TR_PASS);
            },
        ];
    }

    public static function getAvailableActions(\ligneeheros $game): array
    {
        $actions = [PrincipalState::ACTION_PASS];

        if ($game->getPeople()->canHarvestResources()) {
            $actions[] = PrincipalState::ACTION_RESOURCE_HARVEST;
        }

        return $actions;
    }
}
