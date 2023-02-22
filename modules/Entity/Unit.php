<?php

namespace LdH\Entity;

use LdH\Entity\Cards\Disease;

/**
 * @table="meeple"
 */
class Unit implements \JsonSerializable
{
    public const STATUS_FREE  = 'free';
    public const STATUS_MOVED = 'moved';
    public const STATUS_ACTED = 'acted';
    public const STATUS = [
        self::STATUS_FREE,
        self::STATUS_MOVED,
        self::STATUS_ACTED
    ];

    public const LOCATION_MAP       = 'map';
    public const LOCATION_SPELL     = 'spell';
    public const LOCATION_INVENTION = 'invention';
    public const LOCATION = [
        self::LOCATION_MAP,
        self::LOCATION_SPELL,
        self::LOCATION_INVENTION
    ];

    /**
     * @isKey
     * @column="card_id"
     */
    protected int      $id          = 0;

    /**
     * @column="card_type"
     * @entityKey="code"
     */
    protected ?Meeple   $type        = null;

    /**
     * @column="card_location"
     * @enum="LOCATION"
     */
    protected string   $location    = self::LOCATION_MAP;

    /**
     * @column="card_location_arg"
     */
    protected ?int     $locationArg = null;

    /**
     * @column="meeple_status"
     * @enum="STATUS"
     */
    protected string   $status      = self::STATUS_FREE;

    /**
     * @column="meeple_sick"
     * @entityKey="code"
     */
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

    public function getType(): ?Meeple
    {
        return $this->type;
    }

    public function setType(?Meeple $type): self
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
            'type' => $this->type ? $this->type->getCode() : null,
            'type_arg' => 0,
            'nbr' => 1
        ];
    }
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type ? $this->type->getCode() : null,
            'location' => $this->locationArg,
            'status' => $this->getStatus(),
            'disease' => $this->disease ? $this->disease->getCode() : null,
            'lineage' => $this->type && $this->type->getLineage() !== null,
        ];
    }
}