<?php

namespace LdH\Entity\Cards;

class LineageBoardCard extends DefaultBoardCard
{
    /**
     * @column="card_completed"
     */
    protected bool $objectiveCompleted = false;

    /**
     * @column="card_leader"
     */
    protected bool $leader = false;

    public function isObjectiveCompleted(): bool
    {
        return $this->objectiveCompleted;
    }

    public function setObjectiveCompleted(bool $objectiveCompleted): self
    {
        $this->objectiveCompleted = $objectiveCompleted;

        return $this;
    }

    public function isLeader(): bool
    {
        return $this->leader;
    }

    public function setLeader(bool $leader): self
    {
        $this->leader = $leader;

        return $this;
    }

    public static function buildBoardCard(
        string $location = self::LOCATION_DEFAULT,
        int    $locationArg = self::LOCATION_ARG_DEFAULT
    ): BoardCardInterface {
        return (new LineageBoardCard())
            ->setLocation($location)
            ->setLocationArg($locationArg)
        ;
    }
}
