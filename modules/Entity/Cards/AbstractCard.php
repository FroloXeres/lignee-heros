<?php

namespace LdH\Entity\Cards;

abstract class AbstractCard implements CardInterface
{
    public const TYPE_DISEASE     = 'disease';
    public const TYPE_FIGHT     = 'fight';
    public const TYPE_OTHER     = 'other';
    public const TYPE_MAGIC     = 'spell';
    public const TYPE_INVENTION = 'invention';
    public const TYPE_OBJECTIVE = 'objective';
    public const TYPE_LINEAGE   = 'lineage';

    public const LOCATION_DEFAULT  = 'deck';
    public const LOCATION_HAND     = 'hand';
    public const LOCATION_ON_TABLE = 'onTable';
    public const LOCATION_DISCARD  = 'discard';
    public const LOCATION_HIDDEN   = 'hidden';
    public const LOCATION_REMOVED  = 'removed';

    protected string $code         = '';
    protected string $type;
    protected ?int   $type_arg;
    protected string $location     = self::LOCATION_DEFAULT;
    protected ?int   $location_arg;
    protected ?int   $nbr;

    protected string  $name        = '';
    protected string  $description = '';
    protected string  $artist      = '';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
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

    public const TPL_ID        = 'id';
    public const TPL_DECK      = 'deck';
    public const TPL_LARGE     = 'large';
    public const TPL_ICON      = 'icon';
    public const TPL_COST      = 'cost';
    public const TPL_NAME      = 'name';
    public const TPL_TYPE_ICON = 'typeIcon';
    public const TPL_TYPE      = 'type';
    public const TPL_NEED_1    = 'need1';
    public const TPL_NEED_2    = 'need2';
    public const TPL_GAIN      = 'gain';
    public const TPL_TEXT_BOLD = 'textBold';
    public const TPL_TEXT      = 'text';
    public const TPL_ARTIST    = 'artist';

    public const TPL_MEEPLE_POWER    = 'meeplePower';
    public const TPL_OBJECTIVE       = 'objective';
    public const TPL_OBJECTIVE_BONUS = 'objectiveBonus';
    public const TPL_LEAD_TYPE       = 'leadType';
    public const TPL_LEAD_POWER      = 'leadPower';

    public const BGA_TYPE         = 'type';
    public const BGA_TYPE_ARG     = 'type_arg';
    public const BGA_LOCATION     = 'location';
    public const BGA_LOCATION_ARG = 'location_arg';
    public const BGA_NBR          = 'nbr';

    /**
     * Return data for Card module
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::BGA_TYPE         => $this->getType(),
            self::BGA_TYPE_ARG     => $this->getTypeArg(),
            self::BGA_LOCATION     => $this->getLocation(),
            self::BGA_LOCATION_ARG => $this->getLocationArg(),
            self::BGA_NBR          => 1
        ];
    }

    /**
     * Return data for Card template build
     *
     * @param Deck $deck
     *
     * @return array
     */
    public function toTpl(Deck $deck): array
    {
        return [
            self::TPL_ID              => $this->getCode(),
            self::TPL_DECK            => $deck->getType(),
            self::TPL_LARGE           => ($deck->isLarge()? 'large' : ''),
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
            self::TPL_LEAD_POWER      => null,
        ];
    }
}
