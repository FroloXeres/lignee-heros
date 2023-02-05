<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Map\City;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\MessageHelper;
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

                /** @var Lineage $card */
                $card = $this->getDeck(AbstractCard::TYPE_LINEAGE)->getFirstCardByCode($lineage);
                if ($card === null) {
                    throw new \BgaUserException($this->_('This lineage does not exists'));
                }

                // Check if this lineage is free
                $this->getCardService()->updateCardFromDb($card);
                if (!empty($card->getBoardCardsByLocation(BoardCardInterface::LOCATION_HAND))) {
                    throw new \BgaUserException($this->_('Sorry but this lineage has already been taken'));
                }

                // CurrentPlayer choose this lineage
                $playerId = (int) $this->getCurrentPlayerId();
                $card->moveCardsTo(BoardCardInterface::LOCATION_HAND, $playerId);
                $this->getCardService()->updateCard($card);

                // Add lineage Meeple to city (auto-saved)
                $this->getPeople()->birth(
                    $card->getMeeple(),
                    Unit::LOCATION_MAP,
                    PeopleService::CITY_ID
                );

                // Add lineage objective to player's hand
                $objective = $card->getObjective();
                $objective->moveCardsTo(BoardCardInterface::LOCATION_HAND, $playerId);
                $this->getCardService()->updateCard($objective);

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
            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_INVENTIONS,
                clienttranslate('You have discovered two inventions: ${invention1}[i:${id1}] and ${invention2}[i:${id2}].'),
                [
                    'i18n' => ['invention1', 'invention2', 'id1', 'id2'],
                    'invention1' => $cityInventions[0]->getName(),
                    'invention2'=> $cityInventions[1]->getName(),
                    'id1'=> $cityInventions[0]->getCode(),
                    'id2'=> $cityInventions[1]->getCode(),
                ]
            );

            $inventions = $this->cards[AbstractCard::TYPE_INVENTION];
            $this->getCardService()->updateCardsFromDb($inventions);
            $this->notifyAllPlayers(
                GameInitState::NOTIFY_INVENTION_REVEALED,
                clienttranslate('You can research for invention: ${revealed}.'),
                [
                    'i18n' => ['revealed'],
                    'revealed' => MessageHelper::formatList(
                        array_map(
                            function(AbstractCard $card) {
                                return $card->getName();
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
