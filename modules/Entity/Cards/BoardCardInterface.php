<?php

namespace LdH\Entity\Cards;

interface BoardCardInterface
{
    public const BGA_LOCATION     = 'location';
    public const BGA_LOCATION_ARG = 'location_arg';

    public const LOCATION_ARG_DEFAULT  = 0;
    public const LOCATION_DEFAULT  = 'deck';
    public const LOCATION_HAND     = 'hand';
    public const LOCATION_ON_TABLE = 'onTable';
    public const LOCATION_DISCARD  = 'discard';
    public const LOCATION_HIDDEN   = 'hidden';
    public const LOCATION_REMOVED  = 'removed';
    public const LOCATION_PLAYER   = 'player';

    public function getId(): int;
    public function setId(int $id): self;
    public function getLocation(): string;
    public function setLocation(string $location): self;
    public function getLocationArg(): ?int;
    public function setLocationArg(?int $location_arg): self;

    public static function buildBoardCard(string $location = self::LOCATION_DEFAULT, int $locationArg = self::LOCATION_ARG_DEFAULT): BoardCardInterface;
}