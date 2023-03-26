<?php

namespace LdH\State;

use LdH\Entity\Bonus;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\CurrentStateService;


class ScienceHarvestState extends AbstractState
{
    public const ID = 8;
    public const NAME = 'ScienceHarvest';
    public const NOTIFY_SCIENCE_HARVEST = 'ntfyScienceHarvest';

    public const TR_FOOD_HARVEST = 'trFoodHarvest';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Science harvest phase");
        $this->action            = 'st' . $this->name;
        $this->transitions       = [
            self::TR_FOOD_HARVEST => FoodHarvestBonusState::ID,
        ];
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $bonusService = $this->getBonusService();
            $unitRepository = $this->getCardService()->getCardRepository(Unit::class);

            $population = (int) $this->getGameStateValue(CurrentStateService::GLB_PEOPLE_CNT);
            $scienceHarvest = (int) $this->getGameStateValue(CurrentStateService::GLB_SCIENCE_PRD);
            $scienceHarvest *= $bonusService->getLineageBonusesOfType(Bonus::SCIENCE, Bonus::BONUS_MULTIPLY);

            $scienceTiles = $this->getMapService()->getScienceHarvestCodes($this->terrains);
            $scienceHarvestersCount = $unitRepository->getScienceHarvestersCount($scienceTiles, Meeple::SAVANTS);
            $scienceTotalHarvesters = array_sum($scienceHarvestersCount);

            // Population: gives +1 / 5 people
            $populationBonus = floor($population / 5);

            // Lineage meeple or leader bonus
            $scienceBonus = $bonusService->getHarvestScienceBonus(
                function() use ($unitRepository, $scienceTiles) {
                    $warriorScienceHarvestersCount = $unitRepository->getScienceHarvestersCount($scienceTiles, Meeple::WARRIORS);
                    return array_sum($warriorScienceHarvestersCount);
                }
            );

            $scienceToAdd =
                $scienceHarvest * $scienceTotalHarvesters       // Savant harvest
                + $populationBonus                              // + population / 5 bonus
                + $scienceBonus                                 // Meeple bonus
            ;

            if ($scienceToAdd) {
                $this->incGameStateValue(CurrentStateService::GLB_SCIENCE, $scienceToAdd);
                $this->notifyAllPlayers(
                    ScienceHarvestState::NOTIFY_SCIENCE_HARVEST,
                    clienttranslate('${total} [science] harvested'),
                    [
                        'i18n' => ['total'],
                        'savantHarvesters' => $scienceHarvestersCount,
                        'scienceMultiplier' => $scienceHarvest,
                        'populationBonus' => $populationBonus,
                        'lineageBonus' => $scienceBonus,
                        'total' => $scienceToAdd,
                    ]
                );
            }

            $this->gamestate->nextState(ScienceHarvestState::TR_FOOD_HARVEST);
        };
    }
}