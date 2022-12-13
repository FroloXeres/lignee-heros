<?php

namespace LdH\Entity;

use LdH\Entity\Cards\Disease;

class Unit implements \JsonSerializable
{
    public const STATUS_FREE  = 'free';
    public const STATUS_MOVED = 'moved';
    public const STATUS_ACTED = 'acted';

    public const LOCATION_MAP       = 'map';
    public const LOCATION_SPELL     = 'spell';
    public const LOCATION_INVENTION = 'invention';

    protected int      $id;
    protected string   $type;
    protected string   $location    = self::LOCATION_MAP;
    protected ?int     $locationArg = null;
    protected string   $status      = self::STATUS_FREE;
    protected ?Disease $disease     = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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
        return $this->locationArg;
    }

    public function setLocationArg(?int $locationArg): self
    {
        $this->locationArg = $locationArg;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDisease(): ?Disease
    {
        return $this->disease;
    }

    public function setDisease(?Disease $disease): self
    {
        $this->disease = $disease;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_arg' => null,
            'nbr' => 1
        ];
    }
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'location' => $this->locationArg,
            'status' => $this->status,
            'disease' => $this->disease,
        ];
    }
}