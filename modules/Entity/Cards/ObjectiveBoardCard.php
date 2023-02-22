<?php

namespace LdH\Entity\Cards;

class ObjectiveBoardCard extends DefaultBoardCard
{
    /**
     * @column="card_completed"
     */
    protected bool $completed = false;

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public static function buildBoardCard(
        string $location = self::LOCATION_DEFAULT,
        int    $locationArg = self::LOCATION_ARG_DEFAULT
    ): BoardCardInterface {
        return (new ObjectiveBoardCard())
            ->setLocation($location)
            ->setLocationArg($locationArg)
        ;
    }
}
