<?php

namespace LdH\Entity;

class Bonus
{
    const FOOD       = 'food';          // More food produced by meeple/terrain
    const SCIENCE    = 'science';       // More science produced by meeple/terrain
    const POWER      = 'power';         // More power for meeple
    const DRAW_CARTE = 'draw';          // Draw a card of given $type
    const CONVERTER  = 'converter';     // Meeple can convert more (to his type)
    const IS_ALSO    = 'is_also';       // Meeple is also a $type
    const BIRTH      = 'birth';         // Create a new meeple of specified $type

    // Used in CSS
    protected string  $code        = '';
    protected string  $description = '';
    protected int     $count       = 1;
    protected ?string $type        = null;

    /**
     * @param int    $count
     * @param string $code
     */
    public function __construct(int $count, string $code)
    {
        $this->count = $count;
        $this->code  = $code;
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
}
