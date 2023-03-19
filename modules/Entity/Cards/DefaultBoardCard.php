<?php

namespace LdH\Entity\Cards;

class DefaultBoardCard implements BoardCardInterface
{
    /**
     * @column="card_id"
     */
    protected int $id;

    /**
     * @column="card_location"
     */
    protected string $location = BoardCardInterface::LOCATION_DEFAULT;

    /**
     * @column="card_location_arg"
     */
    protected ?int $location_arg;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocationArg(): ?int
    {
        return $this->location_arg;
    }

    public function setLocationArg(?int $location_arg): self
    {
        $this->location_arg = $location_arg;

        return $this;
    }

    public static function buildBoardCard(string $location = self::LOCATION_DEFAULT, int $locationArg = self::LOCATION_ARG_DEFAULT): BoardCardInterface
    {
        return (new DefaultBoardCard())
            ->setLocation($location)
            ->setLocationArg($locationArg)
        ;
    }

    public static function isHiddenCard(AbstractCard $card): bool
    {
        switch (get_class($card)) {
        case Fight::class:
        case Other::class:
        case Disease::class:
            return $card->getAge() !== AbstractCard::AGE_I;
        case Objective::class:
            return $card->isLineageObjective();
        default: break;
        }

        return false;
    }
}