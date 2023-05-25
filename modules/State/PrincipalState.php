<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\Spell;
use LdH\Entity\Map\Resource;
use LdH\Entity\Map\Tile;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Object\FullScreenMessage;
use LdH\Service\AnimationService;
use LdH\Service\CurrentStateService;


class PrincipalState extends AbstractState
{
    public const ID = 5;
    public const NAME = 'Principal';

    public const ACTION_PASS = 'pTurnPass';
    public const ACTION_RESOURCE_HARVEST = 'resourceHarvest';
    public const ACTION_REVEAL_SPELL = 'revealSpell';
    public const ACTION_MASTER_SPELL = 'masterSpell';
    public const ACTION_CAST_SPELL = 'castSpell';
    public const ACTION_MOVE = 'move';
    public const ACTION_EXPLORE = 'explore';
    public const ACTION_RESEARCH = 'research';
    public const ACTION_DISCOVER = 'discover';
    public const ACTION_ACTIVATE = 'activate';

    public const TR_PASS = 'trPass';

    public const NOTIFY_START_TURN = 'ntfyStartTurn';
    public const NOTIFY_RESOURCE_HARVESTED = 'ntfyResourceHarvested';
    public const NOTIFY_SPELL_CARD_DRAWN = 'ntfySpellCardsRevealed';
    public const NOTIFY_SPELL_MASTERED = 'ntfySpellMastered';
    public const NOTIFY_PLAYER_PASS = 'ntfyPlayerPass';
    public const NOTIFY_UNITS_MOVE = 'ntfyUnitsMove';
    public const NOTIFY_TILE_EXPLORED = 'ntfyTileExplored';
    public const NOTIFY_RESEARCHED = 'ntfyResearched';
    public const NOTIFY_DISCOVERED = 'ntfyDiscovered';
    public const NOTIFY_ACTIVATED = 'ntfyActivated';
    public const NOTIFY_SPELL_CASTED = 'ntfySpellCasted';



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
            self::ACTION_REVEAL_SPELL,
            self::ACTION_MASTER_SPELL,
            self::ACTION_MOVE,
            self::ACTION_EXPLORE,
            self::ACTION_CAST_SPELL,
            self::ACTION_RESEARCH,
            self::ACTION_DISCOVER,
            self::ACTION_ACTIVATE,
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
            $playerId = (int) $this->getCurrentPlayerId();
            $args = [
                'actions' => PrincipalState::getAvailableActions($this, null, $playerId),
            ];
            return $this->addPlayersInfosForArgs($args);
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $turn = $this->getCurrentTurn();
            $this->notifyAllPlayers(
                PrincipalState::NOTIFY_START_TURN,
                clienttranslate('[turn] <b>${turn}</b> started'),
                [
                    'i18n' => ['turn'],
                    'turn' => $turn,
                    'fullscreen' => new FullScreenMessage('Turn ' . $turn),
                    'cartridge' => CurrentStateService::getCartridgeUpdate(CurrentStateService::GLB_TURN, $turn)
                ]
            );

            $this->gamestate->setAllPlayersMultiactive();
        };
    }

    public function getActionCleanMethods(): array
    {
        return [
            self::ACTION_PASS => function() {
                /** @var \action_ligneeheros $this */
                $actions = PrincipalState::getAvailableActions($this->game, PrincipalState::ACTION_PASS);
                if (array_key_exists(PrincipalState::ACTION_PASS, $actions)) {
                    $this->game->{PrincipalState::ACTION_PASS}();
                } else {
                    throw new \BgaUserException($this->_('You can\'t pass your turn for now'));
                }
            },
            self::ACTION_MOVE => function() {
                /** @var \action_ligneeheros $this */
                $tileId = (int) $this->getArg('tileId', AT_posint, true);
                $unitIds = $this->getArg('unitIds', AT_json, true);

                if (!is_array($unitIds)) {
                    throw new \BgaUserException($this->_('Unknown units, move impossible'));
                }

                $actions = PrincipalState::getAvailableActions($this->game, PrincipalState::ACTION_MOVE);
                if (array_key_exists(PrincipalState::ACTION_MOVE, $actions)) {
                    $moves = $actions[PrincipalState::ACTION_MOVE]['moves'];
                    foreach ($unitIds as $key => $value) {
                        $unitId = (int) $value;
                        if (!array_key_exists($unitId, $moves) || !in_array($tileId, $moves[$unitId])) {
                            throw new \BgaUserException($this->_('Some unit can\'t move to this tile'));
                        }
                        $unitIds[$key] = $unitId;
                    }

                    $this->game->{PrincipalState::ACTION_MOVE}($unitIds, $tileId);
                } else {
                    throw new \BgaUserException($this->_('You can\'t move any unit anymore'));
                }
            },
            self::ACTION_EXPLORE => function() {
                /** @var \action_ligneeheros $this */
                $tileId = (int) $this->getArg('tileId', AT_posint, true);
                $tile = $this->game->getMapService()->getTileById($tileId);

                $actions = PrincipalState::getAvailableActions($this->game, PrincipalState::ACTION_EXPLORE);
                if (array_key_exists(PrincipalState::ACTION_EXPLORE, $actions) && $tile !== null) {
                    $this->game->{PrincipalState::ACTION_EXPLORE}($tile);
                } else {
                    throw new \BgaUserException($this->_('You can\'t explore for now'));
                }
            },
            self::ACTION_RESEARCH => function() {
                /** @var \action_ligneeheros $this */
                $savantIds = $this->getArg('savantIds', AT_json, true);
                $inventionIds = $this->getArg('inventionIds', AT_json, true);

                // Check if selected savant(s) have not acted and inventions are onTable
                $savants = [];
                $inventions = [];

                $this->game->{PrincipalState::ACTION_RESEARCH}($savants, $inventions);
            },
            self::ACTION_DISCOVER => function() {
                /** @var \action_ligneeheros $this */
                $inventionId = $this->getArg('inventionId', AT_posint, true);

                // Check for science
                $invention = null;

                $this->game->{PrincipalState::ACTION_DISCOVER}($invention);
            },
            self::ACTION_ACTIVATE => function() {
                /** @var \action_ligneeheros $this */
                $inventionId = $this->getArg('inventionId', AT_posint, true);
                $unitIds = $this->getArg('unitIds', AT_json, true);
                $ressourceCodes = $this->getArg('resourceCodes', AT_json, true);

                $invention = null;
                $units = [];
                $ressources = [];

                // Check if we have go those ressources and if units are on city and not acted


                $this->game->{PrincipalState::ACTION_ACTIVATE}($invention, $units, $ressources);
            },

            self::ACTION_REVEAL_SPELL => function() {
                /** @var \action_ligneeheros $this */
                $actions = PrincipalState::getAvailableActions($this->game, PrincipalState::ACTION_REVEAL_SPELL);
                if (array_key_exists(PrincipalState::ACTION_REVEAL_SPELL, $actions)) {
                    $this->game->{PrincipalState::ACTION_REVEAL_SPELL}();
                } else {
                    throw new \BgaUserException($this->_('You can\'t reveal spell(s) this turn anymore'));
                }
            },
            self::ACTION_MASTER_SPELL => function() {
                /** @var \action_ligneeheros $this */
                $actions = PrincipalState::getAvailableActions($this->game, PrincipalState::ACTION_MASTER_SPELL);
                if (array_key_exists(PrincipalState::ACTION_MASTER_SPELL, $actions)) {
                    $spellCode = $this->getArg('spell', AT_alphanum_dash, true);

                    // Check if this spell is onTable
                    $spellDeck = $this->game->cards[AbstractCard::TYPE_MAGIC];
                    $this->game->getCardService()->updateCardsFromDb($spellDeck);

                    $onTableCards = $spellDeck->getCardsOnLocation(BoardCardInterface::LOCATION_ON_TABLE);
                    foreach ($onTableCards as $card) {
                        if ($card->getCode() === $spellCode) {
                            $this->game->{PrincipalState::ACTION_MASTER_SPELL}($onTableCards, $card);
                            return ;
                        }
                    }
                    throw new \BgaUserException($this->game->_('This card is not an authorized spell to choose'));
                }
                throw new \BgaUserException($this->game->_('You can\'t master a spell this turn anymore'));
            },
            self::ACTION_CAST_SPELL => function() {
                /** @var \action_ligneeheros $this */
                $spellId = (int) $this->getArg('spellId', AT_posint, true);

                $spell = null;
                

                // Check if this spell can be cast now
                $this->game->{PrincipalState::ACTION_CAST_SPELL}($spell);
            },
            self::ACTION_RESOURCE_HARVEST => function() {
                /** @var \action_ligneeheros $this */
                $tileId = (int) $this->getArg('tileId', AT_posint, true);
                $unitId = (int) $this->getArg('unitId', AT_posint, true);
                $resourceCode = $this->getArg('resource', AT_alphanum, true);

                $available = $this->game->getPeople()->getHarvestableResources($this->game->terrains);
                if (!array_key_exists($tileId, $available) ||
                    !in_array($unitId, $available[$tileId]->harvesters) ||
                    !array_key_exists($resourceCode, $available[$tileId]->resources) ||
                    $available[$tileId]->resources[$resourceCode] !== false
                ) {
                    throw new \BgaUserException($this->game->_('You can\'t harvest this resource with this unit'));
                } else {
                    $this->game->{PrincipalState::ACTION_RESOURCE_HARVEST}($tileId, $unitId, $resourceCode);
                }
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

            self::ACTION_EXPLORE => function(Tile $tileToExplore) {
                /** @var \ligneeheros $this */
                $toExplore = $this->getMapService()->exploreTile($tileToExplore);

                // No more tiles to explore, pass is now possible
                if (!count($toExplore)) {
                    $this->setGameStateValue(CurrentStateService::GLB_HAVE_TO_EXPLORE, '0');
                }

                $notificationParams = [
                    'i18n' => ['terrain'],
                    'terrain' => $tileToExplore->getTerrain()->getName(),
                    'map' => $this->getMapTiles(),
                    'explore' => $toExplore,
                    'actions' => PrincipalState::getAvailableActions($this),
                ];
                $this->notifyAllPlayers(
                    PrincipalState::NOTIFY_TILE_EXPLORED,
                    clienttranslate('${terrain} has been discovered by your units'),
                    $notificationParams
                );
            },

            self::ACTION_MOVE => function(array $unitIds, int $tileId) {
                /** @var \ligneeheros $this */
                $this->checkAction(PrincipalState::ACTION_MOVE);

                $this->getPeople()->moveUnitsTo($unitIds, $tileId);
                $units = $this->getPeople()->getUnits();

                $tile = $this->getMapService()->getTileById($tileId);
                $explored = $tile->isFlip();
                $notif = sprintf(
                    '${player_name} moves %s to %s tile of coordinate: [%s, %s]',
                    $this->people->getPopulationAsString($unitIds),
                    $explored ? 'the' : 'an unexplored',
                    $tile->getX(),
                    $tile->getY(),
                );

                if (!$explored) {
                    $this->setGameStateValue(CurrentStateService::GLB_HAVE_TO_EXPLORE, '1');
                }

                $this->notifyAllPlayers(
                    PrincipalState::NOTIFY_UNITS_MOVE,
                    clienttranslate($notif),
                    [
                        'i18n' => ['player_name'],
                        'player_name' => $this->getCurrentPlayerName(),
                        'units' => $units,
                        'moves' => $this->getPeopleMoves(),
                        'explore' => $this->getMapService()->getExplorableTiles(),
                    ]
                );
            },

            self::ACTION_RESEARCH => function(array $savants, array  $inventions) {
                /** @var \ligneeheros $this */
                $this->checkAction(PrincipalState::ACTION_RESEARCH);

                /** @var Deck $inventionDeck */
                $inventionDeck = $this->cards[AbstractCard::TYPE_INVENTION];
                $this->getCardService()->updateCardsFromDb($inventionDeck);


                // Draw count($savants) inventions
                // Move selected inventions into Deck and shuffle
                // Savants has acted

                $txt = '${player_name} research some [invention].';
                $this->notifyAllPlayers(PrincipalState::NOTIFY_RESEARCHED, clienttranslate($txt), [
                    'i18n' => ['player_name'],
                    'player_name' => $this->getCurrentPlayerName(),
                    'actions' => PrincipalState::getAvailableActions($this),
                    'units' => [],
                    'cards' => $this->getCardService()->getPublicCards(
                        [AbstractCard::TYPE_INVENTION => $inventionDeck],
                        PrincipalState::ID,
                        (int) $this->getCurrentPlayerId()
                    ),
                ]);
            },

            self::ACTION_DISCOVER => function(Invention $invention) {
                /** @var \ligneeheros $this */
                $this->checkAction(PrincipalState::ACTION_DISCOVER);

                $inventions = $this->cards[AbstractCard::TYPE_INVENTION];
                $this->getCardService()->updateCardsFromDb($inventions);

                // Use invention science cost
                $science = (int) $this->getGameStateValue(CurrentStateService::GLB_SCIENCE);
                $science -= $invention->getScience();

                // Move the card to Hand
                $this->getCardService()->moveTheseCardsTo([$invention]);

                $this->notifyAllPlayers(PrincipalState::NOTIFY_DISCOVERED, clienttranslate('${player_name} use ${science}[science] to discover ${invention}[invention]'), [
                    'i18n' => ['player_name', 'science', 'invention'],
                    'player_name' => $this->getCurrentPlayerName(),
                    'science' => $invention->getScience(),
                    'invention' => $invention->getName(),
                    'actions' => PrincipalState::getAvailableActions($this),
                    'cartridge' => CurrentStateService::getCartridgeUpdate(CurrentStateService::GLB_SCIENCE, $science),
                    'cards' => $this->getCardService()->getPublicCards(
                        [AbstractCard::TYPE_INVENTION => $inventions],
                        PrincipalState::ID,
                        (int) $this->getCurrentPlayerId()
                    ),
                ]);
            },

            // Create object for Cost (Selected unit(s) and/or Resources)
            /**
             * @param array<Unit> $units
             * @param array<Resource> $resources
             */
            self::ACTION_ACTIVATE => function(Invention $activated, array $units, array $resources) {
                /** @var \ligneeheros $this */
                $this->checkAction(PrincipalState::ACTION_ACTIVATE);

                // Use resources
                // Unit have acted
                // Invention is activated
                // Give "gains"

                $uses = [
                    'i18n' => ['uses'],
                    'use ${uses} to'
                ];
                $gains = [
                    'i18n' => ['gains'],
                    'and gain ${gains}'
                ];
                $this->notifyAllPlayers(PrincipalState::NOTIFY_ACTIVATED, clienttranslate('${player_name}${uses} activate ${invention}[invention] and gain ${gains}'), [
                    'i18n' => ['player_name', 'uses', 'gains', 'invention'],
                    'player_name' => $this->getCurrentPlayerName(),
                    'uses' => $uses,
                    'gains' => $gains,
                    'invention' => $activated->getName(),
                    'actions' => PrincipalState::getAvailableActions($this),
                    'cartridge' => CurrentStateService::getCartridgeUpdates([], []),
                    'cards' => $this->getCardService()->getPublicCards(
                        [AbstractCard::TYPE_INVENTION => null],
                        PrincipalState::ID,
                        (int) $this->getCurrentPlayerId()
                    ),
                ]);
            },

            self::ACTION_REVEAL_SPELL => function() {
                /** @var \ligneeheros $this */
                $this->checkAction(PrincipalState::ACTION_REVEAL_SPELL);

                // Prevent reveal
                $this->setGameStateValue(CurrentStateService::GLB_SPELL_REVEALED, '1');

                /** @var Deck $spellDeck */
                $spellDeck = $this->cards[AbstractCard::TYPE_MAGIC];

                // Put max 5 spells from deck to table (1 by mage)
                $units = $this->getPeople()->getByTypeUnits();
                $mageCount = count($units[Meeple::MAGE] ?? []);
                $spellToDraw = min($mageCount, 5);
                $spellDeck->getBgaDeck()->pickCardsForLocation($spellToDraw, BoardCardInterface::LOCATION_DEFAULT, BoardCardInterface::LOCATION_ON_TABLE, 0, true);

                $this->getCardService()->updateCardsFromDb($spellDeck);
                $spellDeck->getCardsOnLocation(BoardCardInterface::LOCATION_ON_TABLE);

                $this->notifyAllPlayers(PrincipalState::NOTIFY_SPELL_CARD_DRAWN, clienttranslate('${player_name} try to master a new [spell]'), [
                    'i18n' => ['player_name'],
                    'player_name' => $this->getCurrentPlayerName(),
                    'actions' => PrincipalState::getAvailableActions($this),
                    'cards' => $this->getCardService()->getPublicCards(
                        [AbstractCard::TYPE_MAGIC => $spellDeck],
                        PrincipalState::ID,
                        (int) $this->getCurrentPlayerId()
                    ),
                ]);
            },

            self::ACTION_MASTER_SPELL => function(array $onTableSpells, AbstractCard $chosenSpell) {
                /** @var \ligneeheros $this */
                $this->checkAction(PrincipalState::ACTION_MASTER_SPELL);

                // Spell mastered this turn, avoid double master
                $this->setGameStateValue(CurrentStateService::GLB_SPELL_MASTERED, '1');

                // Get back cards to spell deck and randomize
                $spellDeck = $this->cards[AbstractCard::TYPE_MAGIC];
                $spellDeck->getBgaDeck()->pickCardsForLocation(count($onTableSpells), BoardCardInterface::LOCATION_ON_TABLE, BoardCardInterface::LOCATION_DEFAULT);

                // Add spell to hand
                $chosenSpell->getBoardCard()->setLocation(BoardCardInterface::LOCATION_HAND);
                $this->getCardService()->updateCard($chosenSpell);

                // Warn everyone
                $this->setGameStateValue(CurrentStateService::GLB_SPELL_REVEALED, '0');

                $playerId = (int) $this->getCurrentPlayerId();
                $this->notifyAllPlayers(
                    PrincipalState::NOTIFY_SPELL_MASTERED,
                    clienttranslate('${player_name} decied to master [spell] ${spell}'),
                    [
                        'i18n' => ['player_name', 'spell'],
                        'player_name' => $this->getCurrentPlayerName(),
                        'spell' => $chosenSpell->getName(),
                        'actions' => PrincipalState::getAvailableActions($this),
                        'cards' => $this->getCardService()->getPublicCards(
                            [AbstractCard::TYPE_MAGIC => $spellDeck],
                            PrincipalState::ID,
                            (int) $this->getCurrentPlayerId()
                        ),
                    ]
                );
            },

            self::ACTION_CAST_SPELL => function(Spell $spell) {
                /** @var \ligneeheros $this */


                $this->notifyAllPlayers(
                    PrincipalState::NOTIFY_SPELL_CASTED,
                    clienttranslate('${player_name} '),
                    [
                        'i18n' => ['player_name'],
                        'player_name' => $this->getCurrentPlayerName(),
                        'actions' => PrincipalState::getAvailableActions($this),
                    ]
                );
            },

            self::ACTION_RESOURCE_HARVEST => function(int $tileId, int $unitId, string $resourceCode) {
                /** @var \ligneeheros $this */
                $tile = $this->getMapTiles()[$tileId];
                $resource = $this->resources[$resourceCode];
                $harvested = $this->getMapService()->harvestResource($tile, $resource);

                if ($harvested) {
                    $unit = $this->getPeople()->getUnitById($unitId);
                    $unit->setStatus(Unit::STATUS_ACTED);
                    $this->getPeople()->getRepository()->update($unit);

                    $resourceState = CurrentStateService::getStateByResource($resource);
                    $count = $this->incGameStateValue($resourceState, 1);

                    $this->notifyAllPlayers(
                        PrincipalState::NOTIFY_RESOURCE_HARVESTED,
                        clienttranslate('${player_name} use ['.$unit->getType()->getCode().'] to harvest ['.$resourceCode.']'),
                        [
                            'i18n' => ['player_name'],
                            'player_name' => $this->getCurrentPlayerName(),
                            'units' => $this->getPeople()->getUnits(),
                            'map' => $this->getPeople()->getHarvestableResources($this->terrains),
                            'animations' => [AnimationService::buildAnimation(AnimationService::TYPE_MOVE_TO_CARTRIDGE, $tileId, $resourceCode, 500)],
                            'cartridge' => CurrentStateService::getCartridgeUpdate($resourceState, $count),
                            'actions' => PrincipalState::getAvailableActions($this)
                        ]
                    );
                } else {
                    throw new \BgaUserException($this->_('You can\'t harvest this resource with this unit'));
                }
            },
        ];
    }

    public static function getBlockingStateActionData(string $state): array
    {
        switch ($state) {
        case CurrentStateService::GLB_SPELL_REVEALED:
            return [
                PrincipalState::ACTION_MASTER_SPELL => [
                    'blocking' => true,
                    'button' => false,
                ]
            ];
        default: return [];
        }
    }

    public static function getAvailableActions(\ligneeheros $game, ?string $action = null, ?int $playerId = null): array
    {
        // Only some actions available when these states
        foreach (CurrentStateService::BLOCKING_STATE as $blockingState) {
            $isInState = $game->getGameStateValue($blockingState) === '1';
            if ($isInState) {
                return PrincipalState::getBlockingStateActionData($blockingState);
            }
        }

        // $playerId = $playerId !== null ? $playerId : (int) $game->getCurrentPlayerId();
        $actions = [];

        if ($action === null || $action === PrincipalState::ACTION_RESOURCE_HARVEST) {
            $list = $game->getPeople()->getHarvestableResources($game->terrains);
            if ($game->getPeople()->hasHarvestableResources($list)) {
                $actions[PrincipalState::ACTION_RESOURCE_HARVEST] = [
                    'button' => clienttranslate('[resources] Harvest'),
                    'blocking' => true,
                    'status' => $list
                ];
            }
        }

        if ($action === null || $action === PrincipalState::ACTION_REVEAL_SPELL) {
            $masteredThisTurn = $game->getGameStateValue(CurrentStateService::GLB_SPELL_MASTERED) === '1';
            $unitsByType = $game->getPeople()->getByTypeUnits();
            if (!$masteredThisTurn && count($unitsByType[Meeple::MAGE] ?? [])) {
                $actions[PrincipalState::ACTION_REVEAL_SPELL] = [
                    'button' => clienttranslate('[spell] Search'),
                    'blocking' => true,
                ];
            }
        }

        $actions[PrincipalState::ACTION_CAST_SPELL] = ['button' => clienttranslate('[spell] Cast')];
        $actions[PrincipalState::ACTION_RESEARCH] = ['button' => clienttranslate('[invention] Research')];
        $actions[PrincipalState::ACTION_DISCOVER] = ['button' => clienttranslate('[invention] Discover')];
        $actions[PrincipalState::ACTION_ACTIVATE] = ['button' => clienttranslate('[invention] Activate')];

        if ($action === null || $action === PrincipalState::ACTION_MOVE) {
            $moves = $game->getPeopleMoves();
            if (!empty($moves)) {
                $actions[PrincipalState::ACTION_MOVE] = [
                    'button' => clienttranslate('[all] Move'),
                    'blocking' => true,
                    'moves' => $moves,
                ];
            }
        }

        $haveToExplore = $game->getGameStateValue(CurrentStateService::GLB_HAVE_TO_EXPLORE) === '1';
        if (($action === null || $action === PrincipalState::ACTION_EXPLORE) && $haveToExplore) {
            $actions[PrincipalState::ACTION_EXPLORE] = [
                'button' => clienttranslate('[explore] Explore'),
                'tiles' => $game->getMapService()->getExplorableTiles(),
            ];
        }

        if (!$haveToExplore && ($action === null || $action === PrincipalState::ACTION_PASS)) {
            $actions[PrincipalState::ACTION_PASS] = clienttranslate('[end_turn] Pass');
        }

        return $actions;
    }
}
