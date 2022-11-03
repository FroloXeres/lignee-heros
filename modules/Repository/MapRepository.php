<?php

namespace LdH\Repository;

use LdH\Entity\Map\City;
use LdH\Entity\Map\Tile;

class MapRepository extends AbstractRepository
{
    protected const TABLE = 'map';

    /**
     * @param Tile[] $tiles
     */
    public function saveMap(array $tiles)
    {
        foreach ($tiles as $tile) {
            $qry = sprintf('INSERT IGNORE INTO `%s`(`tile_id`, `tile_x`, `tile_y`, `tile_revealed`, `tile_disabled`, `tile_far`, `tile_terrain`) VALUES (%s, %s, %s, %s, %s, %s, %s)',
                self::TABLE,
                $tile->getId(),
                $tile->getX(),
                $tile->getY(),
                $tile->isFlip()? '1' : '0',
                $tile->isDisabled()? '1' : '0',
                $tile->getHowFar(),
                $tile->getTerrain()? '"'.$tile->getTerrain()->getCode().'"' : 'NULL'
            );
            $this->query($qry);
        }
    }

    /** Try to get Map from Db, return empty array if not exists */
    public function getMapTiles(bool $onlyRevealed = false): array
    {
        return $this->selectAll(
            'SELECT * FROM `'.self::TABLE.'`' . ($onlyRevealed? ' WHERE `tile_revealed` = 1' : '')
        );
    }

    public function updateCity(City $city): void
    {
        $this->query(
            sprintf(
                'UPDATE `'.self::TABLE.'` SET `tile_terrain` = "%s" WHERE tile_x = 0 AND tile_y = 0',
                $city->getCode()
            )
        );
    }

    public function getTileInfosByPosition(int $x = 0, int $y = 0): array
    {
        return $this->selectAsObject(
            sprintf('SELECT * FROM `map` WHERE `tile_x` = %s AND `tile_y` = %s', $x, $y)
        )[0];
    }
}
