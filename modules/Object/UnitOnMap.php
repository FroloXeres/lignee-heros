<?php

namespace LdH\Object;

class UnitOnMap
{
    public int $tileId = 0;
    public int $count = 0;
    public ?string $terrainCode = null;

    public function __construct(
        int $tileId,
        int $count,
        string $terrain
    ) {
        $this->tileId = $tileId;
        $this->count = $count;
        $this->terrainCode = $terrain;
    }
}