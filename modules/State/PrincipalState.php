<?php

namespace LdH\State;

class PrincipalState extends AbstractState
{
    public const ID = 5;
    public const NAME = 'Principal';

    public const ACTION_PASS = 'pass';
    public const ACTION_UN_PASS = 'unPass';

    public const TR_PASS = 'trPass';
    public const TR_UN_PASS = 'trUnPass';

    public const NOTIFY_PLAYER_PASS = 'playerPass';
    public const NOTIFY_PLAYER_UN_PASS = 'playerUnPass';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate('${actplayer} has to choose an action to do');
        $this->descriptionMyTurn = clienttranslate("Please choose an action to do");
        $this->action            = 'st' . $this->name;
        $this->possibleActions   = [
            self::ACTION_PASS,
            self::ACTION_UN_PASS,
        ];
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            self::TR_UN_PASS => PrincipalState::ID,
            self::TR_PASS => EndTurnState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            // Available actions for this turn
            $args = [
                'actions' => [],
            ];
            $this->addPlayersInfosForArgs($args);

            // Check for available actions


            return $args;
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */


        };
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \action_ligneeheros $this */
                $this->game->{PrincipalState::ACTION_PASS}();
            },
            self::ACTION_UN_PASS => function() {
                /** @var \action_ligneeheros $this */
                $this->game->{PrincipalState::ACTION_UN_PASS}();
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
                    clienttranslate('${player_name} do nothing for now'),
                    $notificationParams
                );

                $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayerId(), PrincipalState::TR_PASS);
            },
            self::ACTION_UN_PASS => function() {
                /** @var \ligneeheros $this */
                // Finally, do something

                $notificationParams = [
                    'i18n' => ['player_name'],
                    'player_name' => $this->getCurrentPlayerName(),
                ];
                $this->notifyAllPlayers(
                    PrincipalState::NOTIFY_PLAYER_UN_PASS,
                    clienttranslate('${player_name} join us back'),
                    $notificationParams
                );

                $this->gamestate->setPlayersMultiactive([$this->getCurrentPlayerId()], PrincipalState::TR_UN_PASS);
            },
        ];
    }
}
