<?php

namespace LdH\Entity;

class Bonus implements \JsonSerializable
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
    public const BONUS_MULTIPLY  = 'multiply';
    public const BIRTH_ALL       = 'birth_choose';    // Choose unit type at birth
    public const SAVANT_HEALING  = 'savant_healing';  // Savant can heal units

    public const INVENTION_FIGHT = 'invention_fight'; // Bonus apply only for invention fight cards



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

    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'description' => $this->description,
            'count' => $this->count
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->description) return $this->description;
        // Else
        $preOp     = '';
        $operation = $this->getType() === Bonus::BONUS_MULTIPLY? 'x' : '+';

        switch ($this->code) {
            case self::POWER:
                $icon = ($this->getType() === Meeple::WARRIOR) ? '['.Meeple::WARRIOR.'] : [power]' : ' : [power]';
                break;
            case self::DEFENSE_CITY:
            case self::DEFENSE_WARRIOR:
            case self::GROWTH:
            case self::MOVE:
                $icon = '['.$this->getCode().']';
                break;
            case self::BIRTH:
                $icon = '['.$this->getType().']';
                break;
            case self::DISTANT_POWER:
                $icon = '[move][power]';
                break;
            case self::STOCK:
                $icon = '[food_stock]';
                break;
            case self::FOOD:
                $icon = sprintf(
                    '%s%s : [food]',
                    $this->getType() ? '' : '[little_end] ',
                    in_array($this->getType(), [Meeple::WARRIOR, Meeple::WORKER]) ? '['.$this->getType().']' : ''
                );
                break;
            case self::FOOD_FOUND:
                $icon = '[food]';
                break;
            case self::SCIENCE:
                $icon = sprintf(
                    '%s%s : [science]',
                    $this->getType()? '' : '[little_end] ',
                    in_array($this->getType(), [Meeple::WARRIOR, Meeple::SAVANT]) ? '['.$this->getType().']' : ''
                );
                break;
            case self::SCIENCE_FOUND:
                $icon = '[science]';
                break;
            case self::RESOURCE:
                $icon      = '['.$this->getType().']';
                $operation = $this->count > 1? 'x' : '';
                break;
            case self::DRAW_CARD:
                $icon      = '[draw]['.$this->getType().']';
                $operation = '';
                break;
            case self::CONVERTER:
                $preOp = ' + ';
            case self::CONVERT:
                $operation = '';
                $count     = $this->getCount() > 1? 'x'.$this->getCount() : '';
                $icon      = sprintf(
                    '[all]%s[little_end]['.$this->getType().']%s',
                    $count,
                    $count
                );
                break;
            case self::DISEASE:
                $operation = '';
                $icon      = '[healing][disease] ' . $this->getType();
                break;
            case self::SPELL_RECAST:
                $operation = '';
                $preOp     = ' ';
                $icon      = ' [mage]x5 : +1 [spell]';
                break;
            case self::MEEPLE_POWER_UP:
                $operation = '';
                $preOp     = ' ';
                $icon      = '[warrior]x5 + [ork_warrior] : [power] +1';
                break;
            default:
                $icon = '[none]';
                break;
        }

        return sprintf('%s%s %s%s',
            $preOp,
            $icon,
            $this->count >= 0? $operation : '',
            ($operation === '+' || ($operation && $this->count > 1))? $this->count : ''
        );
    }
}
