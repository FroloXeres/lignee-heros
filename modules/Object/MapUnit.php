<?php

namespace LdH\Object;

class MapUnit
{
    public int $unitId;
    public int $tileId;
    public Coordinate $position;

    public function __construct(
        int $unitId,
        int $tileId,
        int $x,
        int $y
    ) {
        $this->unitId = $unitId;
        $this->tileId = $tileId;
        $this->position = new Coordinate($x, $y);
    }
}