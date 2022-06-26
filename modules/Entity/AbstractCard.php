<?php

namespace LdH\Entity;

abstract class AbstractCard
{
    public const TYPE_END_TURN  = 'end_turn';
    public const TYPE_EXPLORE   = 'explore';
    public const TYPE_MAGIC     = 'magic';
    public const TYPE_INVENTION = 'invention';
    public const TYPE_OBJECTIVE = 'objective';
    public const TYPE_LINEAGE   = 'lineage';

    public const LOCATION_DEFAULT = 'deck';
    public const LOCATION_HAND    = 'hand';
    public const LOCATION_DISCARD = 'discard';

    protected int    $id;
    protected string $type;
    protected ?int   $type_arg;
    protected string $location     = self::LOCATION_DEFAULT;
    protected ?int   $location_arg;

    protected string  $name        = '';
    protected string  $description = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int|null
     */
    public function getTypeArg(): ?int
    {
        return $this->type_arg;
    }

    /**
     * @param int|null $type_arg
     */
    public function setTypeArg(?int $type_arg): void
    {
        $this->type_arg = $type_arg;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return int|null
     */
    public function getLocationArg(): ?int
    {
        return $this->location_arg;
    }

    /**
     * @param int|null $location_arg
     */
    public function setLocationArg(?int $location_arg): void
    {
        $this->location_arg = $location_arg;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Lineage
     */
    public function setName(string $name): Lineage
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Lineage
     */
    public function setDescription(string $description): Lineage
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Return data for Card module
     *
     * @return array
     */
    abstract public function toArray(): array;
}
