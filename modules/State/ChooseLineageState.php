<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Map\City;
use LdH\Entity\Unit;
use LdH\Service\CurrentStateService;
use LdH\Service\MessageHelper;
use LdH\Service\PeopleService;


class ChooseLineageState extends AbstractState
{
    public const ID = 3;

    public const NAME = 'ChooseLineage';
    public const ACTION_SELECT_LINEAGE = 'selectLineage';
    public const ACTION_CANCEL_LINEAGE = 'cancelLineage';

    public const NOTIFY_PLAYERS_CHOSEN = 'otherPlayerChooseLineage';
    public const NOTIFY_PLAYER_CHOSEN = 'playerChooseLineage';
    public const NOTIFY_OBJECTIVE_DRAW = 'playerDrawObjective';

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
        $this->transitions       = ["" => PrincipalState::ID];
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

                /** @var Lineage $lineageCard */
                $lineageCard = $this->getDeck(AbstractCard::TYPE_LINEAGE)->getFirstCardByCode($lineage);
                if ($lineageCard === null) {
                    throw new \BgaUserException($this->_('This lineage does not exists'));
                }

                // Check if this lineage is free
                $this->getCardService()->updateCardFromDb($lineageCard);
                if (!empty($lineageCard->getBoardCardsByLocation(BoardCardInterface::LOCATION_HAND))) {
                    throw new \BgaUserException($this->_('Sorry but this lineage has already been taken'));
                }

                // CurrentPlayer choose this lineage
                $playerId = (int) $this->getCurrentPlayerId();
                $this->getCardService()->moveTheseCardsTo([$lineageCard], BoardCardInterface::LOCATION_HAND, $playerId);

                // Add lineage Meeple to city (auto-saved)
                $this->incGameStateValue(CurrentStateService::getStateByMeepleType($lineageCard->getMeeple()), 1);
                $lineageUnits = $this->getPeople()->birth(
                    $lineageCard->getMeeple(),
                    Unit::LOCATION_MAP,
                    PeopleService::CITY_ID,
                    1,
                    false
                );

                // Add lineage objective to player's hand
                $objective = $lineageCard->getObjective();
                $this->getCardService()->moveTheseCardsTo([$objective], BoardCardInterface::LOCATION_HAND, $playerId);

                $notificationParams = [
                    'i18n' => ['player_name', 'lineage'],
                    'player_name' => $this->getCurrentPlayerName(),
                    'lineage' => $lineageCard->getName(),
                    'lineageId' => $lineageCard->getCode(),
                    'playerId' => $playerId,
                    'unit' => $lineageUnits[0] ?? [],
                ];
                $this->notifyAllPlayers(
                    ChooseLineageState::NOTIFY_PLAYERS_CHOSEN,
                    clienttranslate('${player_name} will play with ${lineage}'),
                    $notificationParams
                );

                $notificationParams['i18n'] = ['lineage'];
                $this->notifyPlayer(
                    $playerId,
                    ChooseLineageState::NOTIFY_PLAYER_CHOSEN,
                    clienttranslate('You will play with ${lineage}'),
                    $notificationParams
                );

                // Draw second objective card
                $objectives = $this->cards[AbstractCard::TYPE_OBJECTIVE];
                $secondObjective = $this->getCardService()->pickCardForLocation(
                    $objectives,
                    BoardCardInterface::LOCATION_DEFAULT,
                    BoardCardInterface::LOCATION_HAND,
                    $playerId
                );

                // Send objective drawn notification only to you
                $notificationParams = [
                    'i18n' => ['objectiveName'],
                    'objectiveName' => $secondObjective->getName(),
                    'objective' => $secondObjective->toTpl($objectives),
                ];
                $this->notifyPlayer(
                    $playerId,
                    ChooseLineageState::NOTIFY_OBJECTIVE_DRAW,
                    clienttranslate('Your hidden objective will be: ${objectiveName}'),
                    $notificationParams
                );

                // If all player choose Lineage, next step...
                $this->gamestate->setPlayerNonMultiActive($playerId, '');
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

            $peopleService = $this->getPeople();

            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_START,
                clienttranslate('You live in ${city} with ${population}.'),
                [
                    'i18n' => ['city', 'population'],
                    'city' => $city->getName(),
                    'population' => $peopleService->getPopulationAsString(),
                ]
            );

            $cityInventions = array_values($city->getInventions());
            $inventions = $this->cards[AbstractCard::TYPE_INVENTION];
            $this->getCardService()->updateCardsFromDb($inventions);

            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_INVENTIONS,
                clienttranslate('You have discovered two inventions: [${id1}]${invention1} and [${id2}]${invention2} and you can research for ${revealed}.'),
                [
                    'i18n' => ['invention1', 'invention2', 'id1', 'id2', 'others'],
                    'invention1' => $cityInventions[0]->getName(),
                    'invention2'=> $cityInventions[1]->getName(),
                    'id1'=> $cityInventions[0]->getCode(),
                    'id2'=> $cityInventions[1]->getCode(),
                    'revealed' => MessageHelper::formatList(
                        array_map(
                            function(AbstractCard $card) {
                                return sprintf('[%s:%s]', $card->getCode(), $card->getName());
                            },
                            $inventions->getCardsOnLocation(BoardCardInterface::LOCATION_ON_TABLE)
                        )
                    )
                ]
            );

            $this->gamestate->setAllPlayersMultiactive();
        };
    }
}
