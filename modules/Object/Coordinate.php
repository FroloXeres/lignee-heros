<?php

namespace LdH\Object;

class Coordinate {
    public int $x;
    public int $y;

    public function __construct(?int $x = null, ?int $y = null) {
        $x !== null && $this->x = $x;
        $y !== null && $this->y = $y;
    }

    public function key(): string {return $this->x.'_'.$this->y;}

    public function __toString(): string {
        return $this->key();
    }
}