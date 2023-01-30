<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Map\Resource;
use LdH\Entity\Meeple;
use LdH\Entity\Bonus;

/**
 * @table="invention"
 * @entityLinked="\LdH\Entity\Cards\InventionBoardCard"
 */
class Invention extends AbstractCard
{
    public const TYPE_START       = 'start';
    public const TYPE_DEVELOPMENT = 'city';
    public const TYPE_MAGICAL     = 'spell';
    public const TYPE_FIGHT       = 'fight';
    public const TYPE_GROWTH      = 'growth';
    public const TYPE_SCIENCE     = 'science';

    public const CENTER        = 143;
    public const HUT           = 144;
    public const STOCK         = 145;
    public const MOUND         = 146;
    public const STONE_CUTTING = 101;
    public const HUNTING       = 102;
    public const FISHING       = 103;
    public const SCHOOL        = 104;
    public const STONE_CIRCLE  = 105;
    public const TOOLS         = 106;
    public const LONG_BOW      = 107;
    public const POTTERY       = 108;
    public const GRANARY       = 109;
    public const METALLURGY    = 110;
    public const FENCE         = 111;
    public const IRRIGATION    = 112;
    public const DOMESTICATION = 113;
    public const WHEEL         = 114;
    public const OVEN          = 115;
    public const WRITING       = 116;
    public const SOAP          = 117;
    public const BELLOWS       = 118;
    public const GLASS         = 119;
    public const TOILETS       = 120;
    public const BRICKS        = 121;
    public const GRINDSTONE    = 122;
    public const STEEL         = 123;
    public const SHIELD        = 124;
    public const INOCULATION   = 125;
    public const PULLEY        = 126;
    public const CROSSBOW      = 127;
    public const BLAST_FURNACE = 128;
    public const WALL          = 129;
    public const HERBALISM     = 130;
    public const WATER_FILTER  = 131;
    public const ANESTHESIA    = 132;
    public const SEWER         = 133;
    public const ALCHEMY       = 134;
    public const MATHS         = 135;
    public const CLOTHES       = 136;
    public const ROADS         = 137;
    public const COOLER        = 138;
    public const FERMENTING    = 139;
    public const FESTIVAL      = 140;
    public const GEM_CUTTING   = 141;
    public const REARING      = 142;

    // Cost
    /** @var int */
    protected int $science = 0;

    /** @var bool */
    protected bool $or = false;

    /** @var Resource[] */
    protected array $resources = [];

    /** @var Meeple[] */
    protected array $units = [];

    /** @var Bonus[] */
    protected array $gives = [];

    /**
     * @param string $type
     * @param int    $code
     */
    public function __construct(string $type, int $code)
    {
        $this->setType($type);
        $this->setCode($code);
        $this->setCostIcon(self::TYPE_SCIENCE);

        $this->location_arg = 0;
    }

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
        $this->code = self::TYPE_INVENTION . '_' . $code;
        $this->setTypeArg($code);
    }

    /**
     * @return int
     */
    public function getScience(): int
    {
        return $this->science;
    }

    /**
     * @param int $science
     *
     * @return Invention
     */
    public function setScience(int $science): Invention
    {
        $this->science = $science;

        return $this;
    }

    /**
     * @return Resource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param Resource $resource
     *
     * @return $this
     */
    public function addResource(Resource $resource): Invention
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * @param Resource[] $resources
     *
     * @return Invention
     */
    public function setResources(array $resources): Invention
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOr(): bool
    {
        return $this->or;
    }

    /**
     * @param bool $or
     *
     * @return Invention
     */
    public function setOr(bool $or): Invention
    {
        $this->or = $or;

        return $this;
    }

    /**
     * @return Meeple[]
     */
    public function getUnits(): array
    {
        return $this->units;
    }

    /**
     * @param Meeple $meeple
     *
     * @return $this
     */
    public function addUnit(Meeple $meeple): Invention
    {
        $this->units[] = $meeple;

        return $this;
    }

    /**
     * @param Meeple[] $units
     *
     * @return Invention
     */
    public function setUnits(array $units): Invention
    {
        $this->units = $units;

        return $this;
    }

    /**
     * @return Bonus[]
     */
    public function getGives(): array
    {
        return $this->gives;
    }

    /**
     * @param Bonus $bonus
     *
     * @return Invention
     */
    public function addGive(Bonus $bonus): Invention
    {
        $this->gives[] = $bonus;

        return $this;
    }

    /**
     * @param Bonus[] $gives
     */
    public function setGives(array $gives): void
    {
        $this->gives = $gives;
    }

    public static function getTypeName(string $type): string
    {
        return clienttranslate(ucfirst($type));
    }

    public static function getBoardCardClassByCard(): string
    {
        return InventionBoardCard::class;
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
        $tpl = parent::toTpl($deck);

        $tpl[self::TPL_ICON] = Deck::TYPE_INVENTION;
        $tpl[self::TPL_COST] = $this->getScience();
        $tpl[self::TPL_TYPE_ICON] = $this->getType();
        $tpl[self::TPL_TYPE] = self::getTypeName($this->getType());
        $tpl[self::TPL_NEED_1] = join(($this->or ? ' / ' : ''), array_map(function (Meeple $unit) {
            return sprintf('[%s]', $unit->getCode());
        }, $this->getUnits()));
        $tpl[self::TPL_NEED_2] =
            ($this->getUnits() && $this->getResources() ? ' + ' : '') .
            join(($this->or ? ' / ' : ''), array_map(function (Resource $resource) {
                return sprintf('[%s]', $resource->getCode());
            }, $this->getResources()));

        // Description/Gain are the same... See if it works like that
        $giveCount = count($this->gives);
        $tplKey = self::TPL_GAIN_1;
        if ($giveCount > 1) {
            $tpl[self::TPL_GAIN_TYPE] = $this->or ? 'gain_or' : 'gain_and';

            $lastId = count($this->gives) - 1;
            for ($i = 0; $i <= $lastId; $i++) {
                $bonus = $this->gives[$i];
                $tpl[$tplKey] .= $bonus;

                if ($this->or || ($i !== $lastId && $bonus->getCode() !== $this->gives[$i+1]->getCode())) {
                    $tplKey = self::TPL_GAIN_2;
                }
            }
            if ($tplKey === self::TPL_GAIN_1 && !$this->or) {
                $tpl[self::TPL_GAIN_TYPE] = '';
                $tpl[self::TPL_GAIN] = $tpl[self::TPL_GAIN_1];
                $tpl[self::TPL_GAIN_1] = '';
            }
        } else if (!$giveCount) {
            $tpl[self::TPL_GAIN_TYPE] = self::TPL_TYPE_EMPTY;
        } else {
            $tpl[self::TPL_GAIN_TYPE] = '';
            $tpl[self::TPL_GAIN] = join(' ', $this->getGives());
        }
        //$tpl[self::TPL_COMPLETED] = $this->isActivated() ? 'completed' : '';

        return $tpl;
    }
}
