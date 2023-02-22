<?php

namespace LdH\Repository;

use LdH\Entity\Map\City;
use LdH\Entity\Map\Tile;

class MapRepository extends AbstractRepository
{
    /**
     * @param Tile[] $tiles
     */
    public function saveMap(array $tiles)
    {
        foreach ($tiles as $tile) {
            $qry = sprintf('INSERT IGNORE INTO `%s`(%s) VALUES (%s)',
                $this->table,
                join(', ', $this->getFieldNames()),
                join(', ', $this->getFieldValues($tile))
            );
            $this->query($qry);
        }
    }

    /** Try to get Map from Db, return empty array if not exists */
    public function getMapTiles(bool $onlyRevealed = false): array
    {
        return $this->selectAll(
            'SELECT * FROM `'.$this->table.'`' . ($onlyRevealed? ' WHERE `tile_revealed` = 1' : '')
        );
    }

    public function updateCity(City $city): void
    {
        $this->query(
            sprintf(
                'UPDATE `'.$this->table.'` SET `tile_terrain` = "%s" WHERE tile_x = 0 AND tile_y = 0',
                $city->getCode()
            )
        );
    }

    public function getTileInfosByPosition(int $x = 0, int $y = 0): array
    {
        return $this->selectAsObject(
            sprintf('SELECT * FROM `'.$this->table.'` WHERE `tile_x` = %s AND `tile_y` = %s', $x, $y)
        )[0];
    }
}
