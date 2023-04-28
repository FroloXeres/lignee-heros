<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Map\City;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\CurrentStateService;
use LdH\Service\MessageHelper;
use LdH\Service\PeopleService;


class ChooseSpellState extends AbstractState
{
    public const ID = 13;

    public const NAME = 'ChooseSpell';

    public const ACTION_SPELL_CHOSEN = 'spellChosen';

    public const NOTIFY_SPELL_CARD_DRAWN = 'ntfySpellCardsDrawn';
    public const NOTIFY_SPELL_MASTERED = 'ntfySpellMastered';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_ACTIVE;
        $this->description       = clienttranslate("Player have to choose one spell to master.");
        $this->descriptionMyTurn = clienttranslate("Please, choose one spell to master");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->possibleActions   = [
            self::ACTION_SPELL_CHOSEN,
        ];
        $this->transitions       = ["" => PrincipalState::ID];
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_SPELL_CHOSEN => function() {
                /** @var \action_ligneeheros $this */
                $masteredThisTurn = $this->getGameStateValue(CurrentStateService::GLB_SPELL_MASTERED) === '1';
                if ($masteredThisTurn) {
                    // Return user to PrincipalTurn?


                    throw new \BgaUserException($this->game->_('You already mastered a spell this turn'));
                }

                $spellCode = $this->getArg('spell', AT_alphanum_dash, true);

                // Check if this spell is onTable
                $spellDeck = $this->game->cards[AbstractCard::TYPE_MAGIC];
                $this->game->getCardService()->updateCardsFromDb($spellDeck);

                $onTableCards = $spellDeck->getCardsOnLocation(BoardCardInterface::LOCATION_ON_TABLE);
                foreach ($onTableCards as $card) {
                    if ($card->getCode() === $spellCode) {
                        $this->game->{ChooseSpellState::ACTION_SPELL_CHOSEN}($onTableCards, $card);
                        return ;
                    }
                }

                throw new \BgaUserException($this->game->_('This card is not an authorized spell to choose'));
            }
        ];
    }

    public function getActionMethods(): array
    {
        return [
            self::ACTION_SPELL_CHOSEN => function (array $onTableSpells, AbstractCard $chosenSpell) {
                /** @var \ligneeheros $this */
                $this->checkAction(ChooseSpellState::ACTION_SPELL_CHOSEN);

                // Spell mastered this turn, avoid double master
                $this->setGameStateValue(CurrentStateService::GLB_SPELL_MASTERED, true);

                // Get back cards to spell deck and randomize
                $spellDeck = $this->cards[AbstractCard::TYPE_MAGIC];
                $spellDeck->getBgaDeck()->pickCardsForLocation(count($onTableSpells), BoardCardInterface::LOCATION_ON_TABLE, BoardCardInterface::LOCATION_DEFAULT);

                // Add spell to hand
                $chosenSpell->getBoardCard()->setLocation(BoardCardInterface::LOCATION_HAND);
                $this->getCardService()->updateCard($chosenSpell);

                // Warn everyone
                $playerId = (int) $this->getCurrentPlayerId();
                $this->notifyAllPlayers(
                    ChooseSpellState::NOTIFY_SPELL_MASTERED,
                    clienttranslate('${player_name} decied to master [spell] ${spell}'),
                    [
                        'i18n' => ['player_name', 'spell'],
                        'player_name' => $this->getCurrentPlayerName(),
                        'spell' => $chosenSpell->getName(),
                        'actions' => PrincipalState::getAvailableActions($this),
                        'cards' => [
                            AbstractCard::TYPE_MAGIC => [
                                BoardCardInterface::LOCATION_HAND => [
                                    $chosenSpell->toTpl($spellDeck, $playerId)
                                ]
                            ],
                        ],
                    ]
                );

                // Get back user to PrincipalState
                $this->gamestate->setPlayersMultiactive([$playerId], '', false);
            }
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {


            return [
                'i18n' => ['spellChoice'],
                'spellChoice' => clienttranslate('You choose to master spell: ')
            ];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */

            /** @var Deck $spellDeck */
            $spellDeck = $this->cards[AbstractCard::TYPE_MAGIC];

            // Put max 5 spells from deck to table (1 by mage)
            $units = $this->getPeople()->getByTypeUnits();
            $mageCount = count($units[Meeple::MAGE] ?? []);
            $spellToDraw = min($mageCount, 5);
            $spellDeck->getBgaDeck()->pickCardsForLocation($spellToDraw, BoardCardInterface::LOCATION_DEFAULT, BoardCardInterface::LOCATION_ON_TABLE, 0, true);

            $this->getCardService()->updateCardsFromDb($spellDeck);
            $onTableCards = $spellDeck->getCardsOnLocation(BoardCardInterface::LOCATION_ON_TABLE);

            $this->notifyAllPlayers(ChooseSpellState::NOTIFY_SPELL_CARD_DRAWN, clienttranslate('${player_name} try to master a new [spell]'), [
                'i18n' => ['player_name'],
                'player_name' => $this->getCurrentPlayerName(),
                'cards' => [
                    AbstractCard::TYPE_MAGIC => [
                        BoardCardInterface::LOCATION_ON_TABLE => $onTableCards
                    ]
                ]
            ]);
        };
    }
}
