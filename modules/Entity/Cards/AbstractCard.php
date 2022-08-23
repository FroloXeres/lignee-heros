<?php

namespace LdH\Entity\Cards;

abstract class AbstractCard
{
    public const TYPE_DISEASE     = 'disease';
    public const TYPE_FIGHT     = 'fight';
    public const TYPE_OTHER     = 'other';
    public const TYPE_MAGIC     = 'spell';
    public const TYPE_INVENTION = 'invention';
    public const TYPE_OBJECTIVE = 'objective';
    public const TYPE_LINEAGE   = 'lineage';

    public const LOCATION_DEFAULT = 'deck';
    public const LOCATION_HAND    = 'hand';
    public const LOCATION_DISCARD = 'discard';
    public const LOCATION_HIDDEN  = 'hidden';
    public const LOCATION_REMOVED = 'removed';

    protected int    $id;
    protected string $type;
    protected ?int   $type_arg;
    protected string $location     = self::LOCATION_DEFAULT;
    protected ?int   $location_arg;

    protected string  $name        = '';
    protected string  $description = '';
    protected string  $artist      = '';

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
     * @return AbstractCard
     */
    public function setName(string $name): AbstractCard
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
     * @return AbstractCard
     */
    public function setDescription(string $description): AbstractCard
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     *
     * @return AbstractCard
     */
    public function setArtist(string $artist): AbstractCard
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Return data for Card module
     *
     * @return array
     */
    abstract public function toArray(): array;

    public const TPL_ID        = 'ID';
    public const TPL_DECK      = 'DECK';
    public const TPL_ICON      = 'ICON';
    public const TPL_COST      = 'COST';
    public const TPL_NAME      = 'NAME';
    public const TPL_TYPE_ICON = 'TYPE_ICON';
    public const TPL_TYPE      = 'TYPE';
    public const TPL_NEED_1    = 'NEED_1';
    public const TPL_NEED_2    = 'NEED_2';
    public const TPL_GAIN      = 'GAIN';
    public const TPL_TEXT_BOLD = 'TEXT_BOLD';
    public const TPL_TEXT      = 'TEXT';
    public const TPL_ARTIST    = 'ARTIST';

    public const TPL_MEEPLE_POWER    = 'MEEPLE_POWER';
    public const TPL_OBJECTIVE       = 'OBJECTIVE';
    public const TPL_OBJECTIVE_BONUS = 'OBJECTIVE_BONUS';
    public const TPL_LEAD_TYPE       = 'LEAD_TYPE';
    public const TPL_LEAD_POWER      = 'LEAD_POWER';

    /**
     * Return data for Card template build
     *
     * @param string $deck
     *
     * @return array
     */
    public function toTpl(string $deck): array
    {
        return [
            self::TPL_ID              => $this->getId(),
            self::TPL_DECK            => $deck,
            self::TPL_ICON            => 'none',
            self::TPL_COST            => null,
            self::TPL_NAME            => $this->getName(),
            self::TPL_TYPE_ICON       => null,
            self::TPL_TYPE            => null,
            self::TPL_NEED_1          => null,
            self::TPL_NEED_2          => null,
            self::TPL_GAIN            => null,
            self::TPL_TEXT_BOLD       => null,
            self::TPL_TEXT            => $this->getDescription(),
            self::TPL_ARTIST          => $this->getArtist(),
            self::TPL_MEEPLE_POWER    => null,
            self::TPL_OBJECTIVE       => null,
            self::TPL_OBJECTIVE_BONUS => null,
            self::TPL_LEAD_POWER      => null
        ];
    }
}
