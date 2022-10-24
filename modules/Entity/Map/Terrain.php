<?php

namespace LdH\Entity\Map;

use LdH\Entity\Bonus;

/**
 * Class Terrain
 */
class Terrain implements \JsonSerializable
{
    public const TOWN_HUMANIS = 'town_humani';
    public const TOWN_ELVEN   = 'town_elven';
    public const TOWN_NANI    = 'town_nani';
    public const TOWN_ORK     = 'town_ork';

    public const MOUNTAIN         = 'mountain';
    public const MOUNTAIN_LAIR    = 'mountain_lair';
    public const MOUNTAIN_LAKE    = 'mountain_lake';
    public const MOUNTAIN_WOOD    = 'mountain_wood';
    public const MOUNTAIN_TOWER   = 'mountain_tower';
    public const MOUNTAIN_RIVER   = 'mountain_river';
    public const PLAIN            = 'plain';
    public const PLAIN_LAKE       = 'plain_lake';
    public const PLAIN_WOOD       = 'plain_wood';
    public const PLAIN_DESERT     = 'plain_desert';
    public const PLAIN_RIVER_RUIN = 'plain_river_ruin';
    public const HILL             = 'hill';
    public const HILL_PLATEAU     = 'hill_plateau';
    public const HILL_WOOD_RIVER  = 'hill_wood_river';
    public const HILL_RUIN        = 'hill_ruin';
    public const HILL_LAKE        = 'hill_lake';
    public const HILL_WOOD_LAIR   = 'hill_wood_lair';
    public const SWAMP            = 'swamp';
    public const SWAMP_LAIR       = 'swamp_lair';
    public const SWAMP_TOWER      = 'swamp_tower';
    public const FOREST           = 'forest';
    public const FOREST_TOWER     = 'forest_tower';
    public const FOREST_LAIR      = 'forest_lair';
    public const FOREST_DENSE     = 'forest_dense';
    public const FOREST_RUIN      = 'forest_ruin';
    public const DESERT           = 'desert';
    public const DESERT_STONE     = 'desert_stone';

    protected string $name      = '';
    protected string $code      = '';
    protected int    $food      = 0;
    protected bool   $science   = false;

    /**
     * @var Resource[]
     */
    protected array  $resources = [];

    /**
     * @var Bonus[]
     */
    protected array  $bonuses   = [];

    public function __construct(string $name, string $code, int $food = 0, bool $science = false, ?array $resources = [])
    {
        $this->code    = $code;
        $this->name    = $name;
        $this->food    = $food;
        $this->science = $science;

        foreach ($resources as $resource) {
            if ($resource instanceof Resource) {
                $this->resources[$resource->getCode()] = $resource;
            }
        }
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function hasFood(): bool
    {
        return $this->food !== 0;
    }

    /**
     * @return int
     */
    public function getFood(): int
    {
        return $this->food;
    }

    /**
     * @param int $food
     */
    public function setFood(int $food): void
    {
        $this->food = $food;
    }

    /**
     * @return bool
     */
    public function hasScience(): bool
    {
        return $this->science;
    }

    /**
     * @param bool $science
     */
    public function setScience(bool $science): void
    {
        $this->science = $science;
    }

    /**
     * @return Resource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param array $resources
     */
    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * @return array
     */
    public function getBonuses(): array
    {
        return $this->bonuses;
    }

    /**
     * @return Terrain
     */
    public function addBonus(Bonus $bonus): Terrain
    {
        $this->bonuses[] = $bonus;

        return $this;
    }

    /**
     * @param array $bonuses
     */
    public function setBonuses(array $bonuses): void
    {
        $this->bonuses = $bonuses;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'code'      => $this->getCode(),
            'name'      => $this->getName(),
            'food'      => $this->getFood(),
            'science'   => $this->hasScience(),
            'resources' => array_keys($this->getResources()),
            'bonuses'   => $this->getBonuses()
        ];
    }
}
