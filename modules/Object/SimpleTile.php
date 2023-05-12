<?php

namespace LdH\Object;

class SimpleTile {
    public int $tileId;
    public Coordinate $position;
    public bool $revealed = false;

    public function __construct(int $tileId, int $x, int $y, bool $revealed = false) {
        $this->tileId = $tileId;
        $this->position = new Coordinate($x, $y);
        $revealed !== null && $this->revealed = $revealed;
    }

    public function key(): string {return $this->position->key();}

}