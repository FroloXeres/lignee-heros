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
        $this->args              = 'arg' . $this->name;
        $this->transitions       = [
            '' => EndOfEndTurnState::ID,
        ];
    }

    public function getStateArgMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            return [];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $unitRepository = $this->getCardService()->getCardRepository(Unit::class);

            $foodTiles = $this->mapService->getFoodHarvestCodes($this->terrains);
            $foodHarvesters = $unitRepository->getFreeFoodHarvestersOnMap($foodTiles, [Meeple::WORKER, Meeple::HUMANI_WORKER, Meeple::ORK_WORKER]);
            $foodOnMap = $this->getBonusService()->getFoodHarvestedOnMap($foodHarvesters);
            $lineageFoodBonus = $this->getBonusService()->getHarvestFoodBonus(
                function() use ($unitRepository, $foodTiles) {
                    return array_sum(
                        array_map(
                            function(UnitOnMap $unitOnMap) {return $unitOnMap->count;},
                            $unitRepository->getFreeFoodHarvestersOnMap($foodTiles, [Meeple::WARRIOR, Meeple::NANI_WARRIOR, Meeple::ORK_WARRIOR])
                        )
                    );
                }
            );
            $foodToAdd = $foodOnMap + $lineageFoodBonus;

            if ($foodToAdd) {
                $this->incGameStateValue(CurrentStateService::GLB_FOOD, $foodToAdd);
                $this->notifyAllPlayers(
                    FoodHarvestState::NOTIFY_FOOD_HARVEST,
                    clienttranslate('${total} [food] harvested'),
                    [
                        'i18n' => ['total'],
                        'total' => $foodToAdd,
                        'workerHarvesters' => $foodHarvesters,
                        'lineageBonus' => $lineageFoodBonus,
                    ]
                );
            }

            $this->gamestate->nextState();
        };
    }
}