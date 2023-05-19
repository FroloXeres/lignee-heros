<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\LineageBoardCard;
use LdH\Entity\Map\City;
use LdH\Entity\Meeple;
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
                $lineage = $this->getArg('lineage', AT_enum, true, null, Lineage::getLineageCodes());

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
                $stateConst = CurrentStateService::getStateByMeepleType($lineageCard->getMeeple());
                $newCount = $this->incGameStateValue($stateConst, 1);
                $lineageUnits = $this->getPeople()->birth(
                    $lineageCard->getMeeple(),
                    Unit::LOCATION_MAP,
                    PeopleService::CITY_ID,
                    1,
                    false
                );
                $unit = $lineageUnits[0];

                // Link lineage to meeple unit
                $lineageCard->getBoardCard()->setUnit($unit->getId());
                $this->getCardService()->updateCard($lineageCard);

                // Add lineage objective to player's hand
                $objective = $lineageCard->getObjective();
                $this->getCardService()->moveTheseCardsTo([$objective], BoardCardInterface::LOCATION_HAND, $playerId);

                $notificationParams = [
                    'i18n' => ['player_name', 'lineage'],
                    'player_name' => $this->getCurrentPlayerName(),
                    'lineage' => $lineageCard->getName(),
                    'lineageId' => $lineageCard->getCode(),
                    'playerId' => $playerId,
                    'unit' => $unit,
                    'moves' => $this->getPeopleMoves(),
                    'cartridge' => CurrentStateService::getCartridgeUpdate($stateConst, $newCount)
                ];
                $this->notifyAllPlayers(
                    ChooseLineageState::NOTIFY_PLAYER_CHOSEN,
                    clienttranslate('${player_name} will play with [lineage] ${lineage}'),
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
                    clienttranslate('Your hidden [objective] will be: ${objectiveName}'),
                    $notificationParams
                );

                // Check if last player to choose
                $userLeft = self::getUniqueValueFromDB("SELECT COUNT(player_is_multiactive) as nb FROM player");
                if ($userLeft < 2) {
                    ChooseLineageState::drawStartSpell($this);
                }

                // If all player choose Lineage, next step...
                $this->gamestate->setPlayerNonMultiActive($playerId, '');
            }
        ];
    }

    public static function drawStartSpell(\ligneeheros $game): void
    {
        $units = $game->getPeople()->getByTypeUnits();
        if (count($units[Meeple::MAGE])) {
            // Draw one spell
            $spellDeck = $game->cards[AbstractCard::TYPE_MAGIC];
            $spellDeck->getBgaDeck()->pickCardsForLocation(1, BoardCardInterface::LOCATION_DEFAULT, BoardCardInterface::LOCATION_HAND, 0, true);

            $game->getCardService()->updateCardsFromDb($spellDeck);
            $pickedList = $spellDeck->getCardsOnLocation(BoardCardInterface::LOCATION_HAND);

            $picked = end($pickedList);
            $game->notifyAllPlayers(
                GameInitState::NOTIFY_START_SPELL_PICKED,
                clienttranslate('Your mage(s) now master [spell] ${spell}'),
                [
                    'i18n' => ['spell'],
                    'spell' => $picked->getName(),
                    'cards' => [
                        AbstractCard::TYPE_MAGIC => [
                            BoardCardInterface::LOCATION_HAND => $picked
                        ]
                    ]
                ]
            );
        }
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
            $tile = $this->getMapService()->getCentralTile();

            /** @var City $city */
            $city = $tile->getTerrain();

            $peopleService = $this->getPeople();

            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_START,
                clienttranslate('You live in <b>${city}</b> city with '.$peopleService->getPopulationAsString().'.'),
                [
                    'i18n' => ['city', 'population'],
                    'city' => $city->getName(),
                ]
            );

            $inventions = $this->cards[AbstractCard::TYPE_INVENTION];
            $this->getCardService()->updateCardsFromDb($inventions);

            $this->notifyAllPlayers(
                GameInitState::NOTIFY_CITY_INVENTIONS,
                clienttranslate('You have discovered some [invention]: <b>${start}</b> and you can research <b>${revealed}</b>.'),
                [
                    'i18n' => ['start', 'revealed'],
                    'start' => MessageHelper::formatList(
                        array_map(
                            fn(AbstractCard $invention) => $invention->getName(),
                            $inventions->getCardsOnLocation(BoardCardInterface::LOCATION_HAND)
                        )
                    ),
                    'revealed' => MessageHelper::formatList(
                        array_map(
                            fn(AbstractCard $card) => $card->getName(),
                            $inventions->getCardsOnLocation(BoardCardInterface::LOCATION_ON_TABLE)
                        )
                    )
                ]
            );

            $this->gamestate->setAllPlayersMultiactive();
        };
    }
}
