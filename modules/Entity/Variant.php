<?php

namespace LdH\Entity;

class Variant
{
    public const WATER  = 'water';
    public const LAIR   = 'lair';
    public const RUINS  = 'ruins';
    public const TOWER  = 'tower';
    public const PORTAL = 'portal';

    protected string  $code    = '';
    protected array   $bonuses = [];

    /**
     * Possible terrains for this variant
     * @var array
     */
    protected array $terrains = [];

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
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
     * @return Variant
     */
    public function setCode(string $code): Variant
    {
        $this->code = $code;

        return  $this;
    }

    /**
     * @return Bonus[]
     */
    public function getBonuses(): array
    {
        return $this->bonuses;
    }

    /**
     * @param Bonus $bonus
     *
     * @return Variant
     */
    public function addBonus(Bonus $bonus): Variant
    {
        $this->bonuses[] = $bonus;

        return  $this;
    }

    /**
     * @return string[]
     */
    public function getTerrains(): array
    {
        return $this->terrains;
    }

    /**
     * @param string[] $terrains
     *
     * @return Variant
     */
    public function setTerrains(array $terrains): Variant
    {
        $this->terrains = $terrains;

        return  $this;
    }
}
