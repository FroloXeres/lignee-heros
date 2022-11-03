<?php

namespace LdH\Entity\Map;

use LdH\Entity\Cards\Invention;
use LdH\Entity\Meeple;

class City extends Terrain
{
    /**
     * @var Meeple[]
     */
    protected array $units = [];

    /**
     * @var Invention[]
     */
    protected array $inventions = [];

    /**
     * @return Meeple[]
     */
    public function getUnits(): array
    {
        if (count($this->units) !== 2) {
            throw new \Exception(sprintf('City %s must contains exactly 2 units', $this->name));
        }

        return $this->units;
    }

    /**
     * @param Meeple $unit
     *
     * @return $this
     */
    public function addUnit(Meeple $unit): City
    {
        if (count($this->units) < 2) {
            $this->units[] = $unit;
        } else {
            throw new \Exception(sprintf('City %s cannot contains more than 2 units', $this->name));
        }

        return $this;
    }

    /**
     * @return Invention[]
     */
    public function getInventions(): array
    {
        if (count($this->inventions) !== 2) {
            throw new \Exception(sprintf('City %s must give exactly 2 inventions', $this->name));
        }

        return $this->inventions;
    }

    /**
     * @param Invention $invention
     *
     * @return $this
     */
    public function addInvention(Invention $invention): City
    {
        if (count($this->inventions) < 2) {
            $this->inventions[$invention->getCode()] = $invention;
        } else {
            throw new \Exception(sprintf('City %s cannot give more than 2 inventions', $this->name));
        }

        return $this;
    }
}
