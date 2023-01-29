<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Map\City;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\PeopleService;


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

                $playerId = (int) $this->getCurrentPlayerId();

                // Check if this lineage is free


                /** @var Lineage $card */
                $card = $this->getDeck(AbstractCard::TYPE_LINEAGE)->getCardByCode($lineage);
                $card->moveCardsTo(BoardCardInterface::LOCATION_HAND, $playerId);
                $this->getCardService()->updateCard($card, [BoardCardInterface::BGA_LOCATION, BoardCardInterface::BGA_LOCATION_ARG]);

                // Add lineage meeple to city
                $this->getPeople()->birth(
                    $card->getMeeple(),
                    Unit::LOCATION_MAP,
                    PeopleService::CITY_ID
                );

                // Add lineage objective
                $objective = $card->getObjective();
                $objective->moveCardsTo(BoardCardInterface::LOCATION_HAND, $playerId);
                $this->getCardService()->updateCard($objective, [BoardCardInterface::BGA_LOCATION, BoardCardInterface::BGA_LOCATION_ARG]);

                $this->notifyAllPlayers(
                    ChooseLineageState::NOTIFY_PLAYER_CHOSEN,
                    clienttranslate('${player_name} will play with ${lineage}'),
                    [
                        'i18n' => ['player_name', 'lineage'],
                        'player_name' => $this->getCurrentPlayerName(),
                        'lineage' => ($card ? $card->getName() : ''),
                        'id' => $card->getCode(),
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
            $tile = $this->mapService->getCentralTile();

            /** @var City $city */
            $city = $tile->getTerrain();

            $people = $this->getPeople();

            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_START,
                clienttranslate('You live in ${city}'),
                [
                    'i18n' => ['city'],
                    'city' => $city->getName()
                ]
            );

            $cityInventions = array_values($city->getInventions());
            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_INVENTIONS,
                clienttranslate('You have discovered two inventions: ${invention1} and ${invention2}'),
                [
                    'i18n' => ['invention1', 'invention2'],
                    'invention1' => $cityInventions[0]->getName(),
                    'invention2'=> $cityInventions[1]->getName()
                ]
            );
            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_UNITS,
                clienttranslate('${population} lives in ${city}'),
                [
                    'i18n' => ['population', 'city'],
                    'population' => $people->getPopulationAsString(),
                    'city' => $city->getName()
                ]
            );

            $revealed = []; // $this->cards[AbstractCard::TYPE_INVENTION];
            $this->notifyAllPlayers(
                GameInitState::NOTIFY_INVENTION_REVEALED,
                clienttranslate('You can research for invention: ${revealed}'),
                [
                    'i18n' => ['revealed'],
                    'revealed' => join(
                        ', ',
                        array_map(
                            function(AbstractCard $card) {
                                return $card->getName();
                            },
                            $revealed
                        )
                    )
                ]
            );

            $this->gamestate->setAllPlayersMultiactive();
        };
    }
}
