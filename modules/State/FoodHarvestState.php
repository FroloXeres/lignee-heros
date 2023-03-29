<?php

namespace LdH\State;

use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Object\UnitOnMap;
use LdH\Service\CurrentStateService;


class FoodHarvestState extends AbstractState
{
    public const ID = 9;
    public const NAME = 'FoodHarvest';
    public const NOTIFY_FOOD_HARVEST = 'ntfyFoodHarvest';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Food harvest phase");
        $this->action            = 'st' . $this->name;
        $this->transitions       = [
            '' => EndOfEndTurnState::ID,
        ];
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $unitRepository = $this->getCardService()->getCardRepository(Unit::class);

            $foodTiles = $this->getMapService()->getFoodHarvestCodes($this->terrains);
            $foodHarvesters = $unitRepository->getUnitsOnMapByTypeAndNotStatus($foodTiles, Meeple::HARVESTERS, Unit::STATUS_ACTED);
            $foodOnMap = $this->getBonusService()->getFoodHarvestedOnMap($foodHarvesters);
            $lineageFoodBonus = $this->getBonusService()->getHarvestFoodBonus(
                function() use ($unitRepository, $foodTiles) {
                    return array_sum(
                        array_map(
                            function(UnitOnMap $unitOnMap) {return $unitOnMap->count;},
                            $unitRepository->getUnitsOnMapByTypeAndNotStatus($foodTiles, Meeple::WARRIORS, Unit::STATUS_ACTED)
                        )
                    );
                }
            );
            $foodToAdd = $foodOnMap + $lineageFoodBonus;

            if ($foodToAdd) {
                $count = $this->incGameStateValue(CurrentStateService::GLB_FOOD, $foodToAdd);
                $this->notifyAllPlayers(
                    FoodHarvestState::NOTIFY_FOOD_HARVEST,
                    clienttranslate('${total} [food] harvested'),
                    [
                        'i18n' => ['total'],
                        'total' => $foodToAdd,
                        'workerHarvesters' => $foodHarvesters,
                        'lineageBonus' => $lineageFoodBonus,
                        'cartridge' => CurrentStateService::getCartridgeUpdate(CurrentStateService::GLB_FOOD, $count)
                    ]
                );
            }

            $this->gamestate->nextState();
        };
    }
}