<?php

namespace LdH\State;

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
    public const ACTION_MASTER_SPELL = 'masterSpell';

    public const TR_PASS = 'trPass';
    public const TR_CHOOSE_SPELL = 'trChooseSpell';

    public const NOTIFY_PLAYER_PASS = 'ntfyPlayerPass';
    public const NOTIFY_RESOURCE_HARVESTED = 'ntfyResourceHarvested';
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
            self::ACTION_MASTER_SPELL,
        ];
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [

            self::TR_PASS => EndTurnState::ID,
            self::TR_CHOOSE_SPELL => ChooseSpellState::ID,
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
                $this->game->{PrincipalState::ACTION_PASS}();
            },
            self::ACTION_MASTER_SPELL => function() {
                /** @var \action_ligneeheros $this */
                $this->game->{PrincipalState::ACTION_MASTER_SPELL}();
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
            self::ACTION_MASTER_SPELL => function() {
                /** @var \ligneeheros $this */
                $actions = PrincipalState::getAvailableActions($this, PrincipalState::ACTION_MASTER_SPELL);
                if (array_key_exists(PrincipalState::ACTION_MASTER_SPELL, $actions)) {
                    $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayerId(), PrincipalState::TR_CHOOSE_SPELL);
                } else {
                    throw new \BgaUserException($this->_('You can\'t master a spell this turn anymore'));
                }
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
                            'animation' => AnimationService::buildAnimation(AnimationService::TYPE_MOVE_TO_CARTRIDGE, $tileId, $resourceCode, 500),
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

    public static function getAvailableActions(\ligneeheros $game, ?string $action = null): array
    {
        $actions = [];

        if ($action === null || $action === PrincipalState::ACTION_RESOURCE_HARVEST) {
            $list = $game->getPeople()->getHarvestableResources($game->terrains);
            if (count($list)) {
                $actions[PrincipalState::ACTION_RESOURCE_HARVEST] = [
                    'button' => clienttranslate('Harvest'),
                    'status' => $list
                ];
            }
        }

        if ($action === null || $action === PrincipalState::ACTION_MASTER_SPELL) {
            $masteredThisTurn = $game->getGameStateValue(CurrentStateService::GLB_SPELL_MASTERED) === '1';
            $unitsByType = $game->getPeople()->getByTypeUnits();
            if (!$masteredThisTurn && count($unitsByType[Meeple::MAGE] ?? [])) {
                $actions[PrincipalState::ACTION_MASTER_SPELL] = [
                    'button' => clienttranslate('Master spell'),
                ];
            }
        }

        if ($action === null || $action === PrincipalState::ACTION_PASS) {
            $actions[PrincipalState::ACTION_PASS] = clienttranslate('Pass');
        }

        return $actions;
    }
}
