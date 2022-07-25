<?php

namespace LdH\Entity\Map;

/**
 * Class Terrain
 */
class Terrain implements \JsonSerializable
{
    public const TOWN_HUMANIS = 'town_humani';
    public const TOWN_ELVEN   = 'town_elven';
    public const TOWN_NANI    = 'town_nani';
    public const TOWN_ORK     = 'town_ork';

    public const MOUNTAIN  = 'mountain';
    public const PLAIN     = 'plain';
    public const HILL      = 'hill';
    public const SWAMP     = 'swamp';
    public const WOOD      = 'wood';
    public const FOREST    = 'forest';
    public const TROPICAL  = 'tropical';
    public const DESERT    = 'desert';
    public const GRASSLAND = 'grassland';
    public const LAKE      = 'lake';
    public const PLATEAU   = 'plateau';

    protected string $name      = '';
    protected string $code      = '';
    protected bool   $food      = false;
    protected array  $resources = [];

    public function __construct(string $name, string $code, bool $food = false, ?array $resources = [])
    {
        $this->code = $code;
        $this->name = $name;
        $this->food = $food;

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
        return $this->food;
    }

    /**
     * @param bool $food
     */
    public function setFood(bool $food): void
    {
        $this->food = $food;
    }

    /**
     * @return array
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
    public function jsonSerialize(): array
    {
        return [
            'code'      => $this->getCode(),
            'name'      => $this->getName(),
            'resources' => json_encode($this->getResources())
        ];
    }
}
