<?php

namespace LdH\Object;

class TileHarvestResources
{
    public int $tileId;

    public string $terrain;
    
    /** @var array<int> */
    public array $harvesters = [];

    /** @var array<int> */
    public array $resources = [null, null, null];
}