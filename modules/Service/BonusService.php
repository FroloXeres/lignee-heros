<?php

namespace LdH\Service;

use LdH\Entity\Bonus;
use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Meeple;
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

    public function __construct(
        \ligneeheros $game,
        bool $isLeaderPowerTurn = false
    ) {
        $this->game = $game;
        $this->isLeaderPowerTurn = $isLeaderPowerTurn;
    }

    //
    public function getHarvestFoodBonus() {
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

    public function getHarvestScienceBonus(?callable $warriorBonus = null): int {
        $total = 0;

        $bonuses = $this->getLineageBonuses();
        foreach ($bonuses as $bonus) {
            if ($bonus->getCode() === Bonus::SCIENCE && !$bonus->getType()) {
                $total += $bonus->getCount();
            }
        }

        if ($this->hasWarriorHarvestTurn()) {
            $total += $warriorBonus();
        }

        return $total;
    }

    public function getHarvestScienceMultiplier(): int
    {
        $total = 1.0;
        $bonuses = $this->getLineageBonuses();
        foreach ($bonuses as $bonus) {
            if ($bonus->getCode() === Bonus::SCIENCE && $bonus->getType() === Bonus::BONUS_MULTIPLY) {
                $total *= $bonus->getCount();
            }
        }
        return $total;
    }

    public function hasWarriorHarvestTurn(): bool
    {
        $bonuses = $this->getLineageBonuses();
        foreach ($bonuses as $bonus) {
            if ($bonus->getCode() === Bonus::SCIENCE && $bonus->getType() === Meeple::WARRIOR) {
                return true;
            }
        }
        return false;
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