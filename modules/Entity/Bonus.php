<?php

namespace LdH\Entity;

class Bonus
{
    public const FOOD            = 'food';            // More food produced by meeple/terrain
    public const FOOD_FOUND      = 'food_found';      // Food put into stock
    public const SCIENCE         = 'science';         // More science produced by meeple/terrain
    public const SCIENCE_FOUND   = 'science_found';   // Science put into stock
    public const STOCK           = 'stock';           // Increase food stock
    public const GROWTH          = 'growth';          // Increase growth
    public const DISEASE         = 'disease';         // Protect from disease of specified level (count)
    public const POWER           = 'power';           // More power for meeple
    public const DISTANT_POWER   = 'distant_power';   // More power for distant meeple
    public const DEFENSE_WARRIOR = 'defense_warrior'; // Defense warrior
    public const DEFENSE_CITY    = 'defense_city';    // Defense city
    public const DRAW_CARD       = 'draw';            // Draw a card of given $type
    public const CONVERT         = 'convert';         // Can convert meeple to specified type
    public const CONVERTER       = 'converter';       // Meeple can convert more (to his type)
    public const IS_ALSO         = 'is_also';         // Meeple is also a $type
    public const BIRTH           = 'birth';           // Create a new meeple of specified $type
    public const RESOURCE        = 'resource';        // Give resource
    public const MOVE            = 'move';            // More move
    public const SPELL_RECAST    = 'recast';          // Can cast another spell
    public const MEEPLE_POWER_UP = 'power_up';        // Meeple increase +1 Power for Warriors

    public const BONUS_MULTIPLY = 'multiply';

    // Used in CSS
    protected string  $code        = '';
    protected string  $description = '';
    protected int     $count       = 1;
    protected ?string $type        = null;

    /**
     * @param int     $count
     * @param string  $code
     * @param ?string $type
     */
    public function __construct(int $count, string $code, ?string $type = null)
    {
        $this->count = $count;
        $this->code  = $code;
        $this->type  = $type;
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
     * @return Bonus
     */
    public function setCode(string $code): Bonus
    {
        $this->code = $code;

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
     * @return Bonus
     */
    public function setDescription(string $description): Bonus
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return Bonus
     */
    public function setCount(int $count): Bonus
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return Bonus
     */
    public function setType(?string $type): Bonus
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->description) return $this->description;
        // Else

        $operation = '+';

        switch ($this->code) {
        case self::FOOD:
        case self::FOOD_FOUND:
            $icon = '[.icon.cube.food]';
            break;
        case self::SCIENCE:
        case self::SCIENCE_FOUND:
            $icon = '[.icon.cube.science]';
            break;
        case self::RESOURCE:
            $icon      = '[.icon.cube.'.$this->getType().']';
            $operation = $this->count > 1? 'x' : '';
            break;
        default:
            $icon = '[none]';
            break;
        }

        return sprintf('%s %s%s',
            $icon,
            $operation,
            ($operation === '+' || $this->count > 1)? $this->count : ''
        );
    }
}
