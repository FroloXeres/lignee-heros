<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Bonus;
use LdH\Entity\Map\Resource;

class Spell extends AbstractCard
{
    public const WHEN_FIGHT_START_ROUND = 1;
    public const WHEN_FIGHT_END_ROUND   = 2;
    public const WHEN_EXPLORE_OTHER     = 3;
    public const WHEN_EXPLORE_DISEASE   = 4;
    public const WHEN_DIE_THROW         = 5;
    public const WHEN_TURN_ANY          = 6;
    public const WHEN_GROWTH            = 7;
    public const WHEN_FOOD_HARVEST      = 8;
    public const WHEN_RESOURCE_HARVEST  = 9;

    public const TARGET_NO           = 0;
    public const TARGET_RESOURCE_ANY = 1;
    public const TARGET_FIGHT_POWER  = 2;
    public const TARGET_MONSTER      = 3;
    public const TARGET_UNIT_WARRIOR = 4;
    public const TARGET_UNIT_WORKER  = 5;
    public const TARGET_UNIT_ANY     = 6;
    public const TARGET_DICE         = 7;
    public const TARGET_TILE         = 8;

    public const RANGE_NO      = 0;
    public const RANGE_TILE    = 1;
    public const RANGE_NEARBY  = 2;
    public const RANGE_CITY    = 3;
    public const RANGE_ENDLESS = 4;

    public const TIMES_UNLIMITED = 0;
    public const TIMES_ONE       = 1;

    public const EFFECT_FLIP          = 1;
    public const EFFECT_HEAL          = 2;
    public const EFFECT_CURE          = 3;
    public const EFFECT_DIE           = 4;
    public const EFFECT_CANCEL        = 5;
    public const EFFECT_WATCH         = 7;
    public const EFFECT_HARVEST_TWICE = 8;
    public const EFFECT_NEW_RESOURCE  = 9;

    public const TYPE_FORESIGHT = 'foresight';
    public const TYPE_COMBAT    = 'fight';
    public const TYPE_NATURE   = 'nature';
    public const TYPE_ENCHANT  = 'enchant';
    public const TYPE_HEALING  = 'healing';

    public const HEAL              = 601;
    public const CURE              = 602;
    public const MAGIC_MISSILE     = 603;
    public const FIRE_CONTROL      = 604;
    public const LIGHTNING         = 605;
    public const METEOR            = 606;
    public const GROUPED_HEAL      = 607;
    public const SACRIFICE         = 608;
    public const WEAKNESS          = 609;
    public const LIANA_PRISON      = 610;
    public const ENCHANT           = 611;
    public const GREAT_ENCHANT     = 612;
    public const FOG               = 613;
    public const SHARP_EYE         = 614;
    public const YOUTH             = 615;
    public const LUKE              = 616;
    public const PROBABILITY       = 617;
    public const GENIUS            = 618;
    public const GROWTH            = 619;
    public const FERTILE_LAND      = 620;
    public const WEATHER_CONTROl   = 621;
    public const CREATION          = 622;
    public const STAMINA           = 623;
    public const ANIMAL_FRIENDSHIP = 624;
    public const CLEAR_VISION      = 625;
    public const GREAT_CURE        = 626;
    public const EXHAUSTING        = 627;

    protected int       $code;
    protected int       $when        = self::WHEN_TURN_ANY;
    protected int       $target      = self::TARGET_NO;
    protected int       $targetCount = 1;
    protected int       $range       = self::RANGE_TILE;
    protected int       $casterCount = 1;
    protected ?Resource $cost        = null;
    protected ?int      $effect      = null;
    protected int       $times       = self::TIMES_UNLIMITED;

    /**
     * @var Bonus[]
     */
    protected array $gives;

    /**
     * @param string $type
     * @param int    $code
     */
    public function __construct(string $type, int $code)
    {
        $this->setType($type);
        $this->setCode($code);

        // Card specific
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
     *
     * @return Spell
     */
    public function setCode(string $code): Spell
    {
        $this->code = self::TYPE_MAGIC . '_' . $code;
        $this->setTypeArg($code);

        return $this;
    }

    /**
     * @return int
     */
    public function getWhen(): int
    {
        return $this->when;
    }

    /**
     * @param int $when
     *
     * @return Spell
     */
    public function setWhen(int $when): Spell
    {
        $this->when = $when;

        return $this;
    }

    /**
     * @return int
     */
    public function getTarget(): int
    {
        return $this->target;
    }

    /**
     * @param int $target
     *
     * @return Spell
     */
    public function setTarget(int $target): Spell
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return int
     */
    public function getTargetCount(): int
    {
        return $this->targetCount;
    }

    /**
     * @param int $targetCount
     *
     * @return Spell
     */
    public function setTargetCount(int $targetCount): Spell
    {
        $this->targetCount = $targetCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRange(): int
    {
        return $this->range;
    }

    /**
     * @param int $range
     *
     * @return Spell
     */
    public function setRange(int $range): Spell
    {
        $this->range = $range;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEffect(): ?int
    {
        return $this->effect;
    }

    /**
     * @param int|null $effect
     *
     * @return Spell
     */
    public function setEffect(?int $effect): Spell
    {
        $this->effect = $effect;

        return $this;
    }

    /**
     * @return int
     */
    public function getCasterCount(): int
    {
        return $this->casterCount;
    }

    /**
     * @param int $casterCount
     *
     * @return Spell
     */
    public function setCasterCount(int $casterCount): Spell
    {
        $this->casterCount = $casterCount;

        return $this;
    }

    /**
     * @return Resource|null
     */
    public function getCost(): ?Resource
    {
        return $this->cost;
    }

    /**
     * @param Resource|null $cost
     *
     * @return Spell
     */
    public function setCost(?Resource $cost): Spell
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimes(): int
    {
        return $this->times;
    }

    /**
     * @param int $times
     *
     * @return Spell
     */
    public function setTimes(int $times): Spell
    {
        $this->times = $times;

        return $this;
    }

    /**
     * @return array
     */
    public function getGives(): array
    {
        return $this->gives;
    }

    /**
     * @param Bonus $give
     *
     * @return Spell
     */
    public function addGive(Bonus $give): Spell
    {
        $this->gives[] = $give;

        return $this;
    }

    /**
     * @param Bonus[] $gives
     *
     * @return Spell
     */
    public function setGives(array $gives): Spell
    {
        $this->gives = $gives;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getTypeAsText(string $type): string
    {
        return clienttranslate(ucfirst($type));
    }

    /**
     * @param int $count
     *
     * @return string
     */
    public static function getCasterAsIcon(int $count): string
    {
        return join(
            ' ',
            array_fill(
                0,
                $count,
                '[.icon.cube.mage]'
            )
        );
    }

    /**
     * Return data for Card module
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'     => $this->getType(),
            'type_arg' => $this->getTypeArg(),
            'nbr'      => 1
        ];
    }

    /**
     * Return data for Card template build
     *
     * @param string $deck
     *
     * @return array
     */
    public function toTpl(string $deck): array
    {
        $tpl = parent::toTpl($deck);

        $tpl[self::TPL_ICON]      = Deck::TYPE_MAGIC;
        $tpl[self::TPL_TYPE_ICON] = $this->getType();
        $tpl[self::TPL_TYPE]      = self::getTypeAsText($this->getType());
        $tpl[self::TPL_NEED_1]    = self::getCasterAsIcon($this->getCasterCount());
        $tpl[self::TPL_NEED_2]    = $this->getCost()? '[.icon.cube.'.$this->getCost()->getCode().']' : null;

        return $tpl;
    }
}
