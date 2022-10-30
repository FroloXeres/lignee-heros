<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Lineage;

class ChooseLineageState extends AbstractState
{
    public const ID = 3;

    public const NAME = 'ChooseLineage';
    public const ACTION_SELECT_LINEAGE = 'selectLineage';
    public const ACTION_CANCEL_LINEAGE = 'cancelLineage';

    public const NOTIFY_PLAYER_CHOSEN = 'playerChooseLineage';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_MULTI_ACTIVE;
        $this->description       = clienttranslate("Everyone have to choose the lineage they will play with.");
        $this->descriptionMyTurn = clienttranslate("Please, select your lineage");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [self::ACTION_SELECT_LINEAGE, self::ACTION_CANCEL_LINEAGE];
        $this->transitions       = ["" => DrawObjectiveState::ID];
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_SELECT_LINEAGE => function() {
                /** @var \action_ligneeheros $this */
                $lineage = $this->getArg('lineage', AT_enum, true, null, Lineage::getLineageIds());

                $this->game->{ChooseLineageState::ACTION_SELECT_LINEAGE}($lineage);
            }
        ];
    }

    public function getActionMethods(): array
    {
        return [
            self::ACTION_SELECT_LINEAGE => function ($lineage) {
                /** @var \ligneeheros $this */
                $this->checkAction(ChooseLineageState::ACTION_SELECT_LINEAGE);

                $card = $this->getDeck(AbstractCard::TYPE_LINEAGE)->getCardByCode($lineage);

                $this->notifyAllPlayers(
                    ChooseLineageState::NOTIFY_PLAYER_CHOSEN,
                    clienttranslate('${player_name} will play with ${lineage}'),
                    [
                        'i18n' => ['player_name', 'lineage'],
                        'player_name' => $this->getActivePlayerName(),
                        'lineage' => ($card ? $card->getName() : '')
                    ]
                );

                // If all player choose Lineage, next step...
                $this->gamestate->setPlayerNonMultiActive($this->getCurrentPlayerId(), '');
            }
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            return [
                'i18n' => ['lineageChoice'],
                'lineageChosen' => clienttranslate('You choose to play with lineage: ')
            ];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */

            // Notify for GameInit choices
            //$this->notifyAllPlayers('');

            $this->gamestate->setAllPlayersMultiactive();
        };
    }
}
