<?php

namespace LdH\Service;

use LdH\Entity\Bonus;
use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Meeple;
use LdH\Object\UnitOnMap;
use LdH\ObjectiveStat;
use LdH\Repository\AbstractCardRepository;
use LdH\Repository\CardRepository;


class BonusService
{
    protected \ligneeheros $game;
    protected bool $isLeaderPowerTurn;

    protected ?AbstractCardRepository $objectiveRepo = null;
    protected ?ObjectiveStat $objectiveStats = null;

    /** @var array<Bonus> */
    protected ?array $lineageBonuses = null;

    public function __construct(\ligneeheros $game) {
        $this->game = $game;
        $this->isLeaderPowerTurn = $game->isLeaderPowerTurn();
    }

    //
    public function getHarvestFoodBonus2() {
        //$tiles = $this->game->getMapTiles();
        //foreach ($freeSavantMap as $tileId => $count) {
        //    $bonuses = $tiles[$tileId]->getTerrain()->getBonuses();
        //    foreach ($bonuses as $bonus) {
        //        if ($bonus->getCode() === Bonus::SCIENCE) {
        //
        //        }
        //    }
        //}
    }

    public function getHarvestFoodBonus(callable $warriorBonus): int {
        $total = $this->getLineageBonusesOfType(Bonus::FOOD);

        if ($this->hasWarriorHarvestTurn(Bonus::FOOD)) {
            $total += $warriorBonus();
        }

        return $total;
    }

    public function getLineageBonusesOfType(string $code, ?string $type = null): int {
        $multiply = in_array($type, [Bonus::BONUS_MULTIPLY], true);
        $total = $multiply ? 1 : 0;
        $bonuses = $this->getLineageBonuses();
        foreach ($bonuses as $bonus) {
            if ($bonus->getCode() === $code && $bonus->getType() === $type) {
                $total = $multiply ?
                    $total * $bonus->getCount() :
                    $total + $bonus->getCount()
                ;
            }
        }
        return $total;
    }

    public function getHarvestScienceBonus(callable $warriorBonus): int {
        $total = $this->getLineageBonusesOfType(Bonus::SCIENCE);

        if ($this->hasWarriorHarvestTurn()) {
            $total += $warriorBonus();
        }

        return $total;
    }

    public function hasWarriorHarvestTurn(string $bonusCode = Bonus::SCIENCE): bool
    {
        $bonuses = $this->getLineageBonuses();
        foreach ($bonuses as $bonus) {
            if ($bonus->getCode() === $bonusCode && $bonus->getType() === Meeple::WARRIOR) {
                return true;
            }
        }
        return false;
    }

    /** @param array<UnitOnMap> $foodHarvesters */
    public function getFoodHarvestedOnMap(array $foodHarvesters): int
    {
        $foodHarvest = (int) $this->game->getGameStateValue(CurrentStateService::GLB_FOOD_PRD);

        $foodToAdd = 0;
        foreach ($foodHarvesters as $tileId => $tileInfo) {
            $terrain = $this->game->terrains[$tileInfo->terrainCode];

            $tileFoodBonus = 0;
            foreach ($terrain->getBonuses() as $bonus) {
                if ($bonus->getCode() === Bonus::FOOD && !$bonus->getType()) {
                    $tileFoodBonus += $bonus->getCount();
                }
            }

            $foodToAdd += min($terrain->getFood(), $tileInfo->count) * ($foodHarvest + $tileFoodBonus);
        }

        return $foodToAdd;
    }

    public function getLineageBonuses(): array
    {
        if ($this->lineageBonuses === null) {
            $this->lineageBonuses = [];
            $lineageRepository = $this->game->getCardService()->getCardRepository(Lineage::class);

            $lineages = $this->game->cards[AbstractCard::TYPE_LINEAGE];
            $lineageRepository->updateCardsFromDb($lineages->getCards());

            // Objective bonus for each completed
            $objectiveStat = $this->getObjectivesStats();

            // Meeples bonus
            foreach ($lineages->getCards() as $card) {
                /** @var Lineage $card */
                $boardCard = $card->getBoardCard();
                if ($boardCard->getLocation() === BoardCardInterface::LOCATION_HAND) {
                    $isFree = $this->game->getPeople()->isLineageUnitFree($card->getMeeple()->getCode());
                    if ($isFree) {
                        $this->lineageBonuses[] = $card->getMeeplePower();

                        $count = $objectiveStat->getPlayerObjectiveCount($boardCard->getLocationArg());
                        for ($i = 0; $i < $count; $i++) {
                            $this->lineageBonuses[] = $card->getObjectiveBonus();
                        }
                    }
                }
            }

            // Leader bonus
            if ($this->isLeaderPowerTurn) {
                $leaderId = (int) $this->game->getGameStateValue(CurrentStateService::GLB_LEADER);
                if ($leaderId) {
                    /** @var Lineage $leader */
                    $leaderCode = Lineage::getCodeByStateId($leaderId);
                    $leader = $lineages->getFirstCardByKey($leaderCode);
                    $this->lineageBonuses[] = $leader->getLeadingBonus();
                }
            }
        }

        return $this->lineageBonuses;
    }

    public function getObjectivesStats(): ObjectiveStat
    {
        if ($this->objectiveStats === null) {
            $this->objectiveStats = new ObjectiveStat(
                $this->getObjectiveRepo()->getCompletedObjectives()
            );
        }

        return $this->objectiveStats;
    }

    public function getObjectiveRepo(): CardRepository
    {
        if ($this->objectiveRepo === null) {
            $this->objectiveRepo = $this->game->getCardService()->getCardRepository(Objective::class);
        }

        return $this->objectiveRepo;
    }
}