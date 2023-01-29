<?php

namespace LdH\Entity\Cards;

class InventionBoardCard extends DefaultBoardCard
{
    /**
     * @column="card_activated"
     */
    protected bool $activated = false;

    public function __construct()
    {
        $this->setLocationArg(0);
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public static function buildBoardCard(
        string $location = self::LOCATION_DEFAULT,
        int    $locationArg = self::LOCATION_ARG_DEFAULT
    ): BoardCardInterface {
        return (new InventionBoardCard())
            ->setLocation($location)
            ->setLocationArg($locationArg)
        ;
    }
}
