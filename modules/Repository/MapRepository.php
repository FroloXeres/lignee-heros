<?php

namespace LdH\Repository;

use LdH\Entity\Map\Tile;

class MapRepository
{
    /**
     * @param array $map
     * @param bool  $creation
     *
     * @return string[]
     */
    public static function getSaveQueries(array $map, bool $creation = true): array
    {
        $queries = [];

        /** @var Tile $tile */
        foreach ($map as $tile) {
            $queries[] = $creation?
                sprintf('INSERT IGNORE INTO `map`(`tile_id`, `tile_x`, `tile_y`, `tile_revealed`, `tile_disabled`, `tile_far`, `tile_terrain`, `tile_variant`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)',
                    $tile->getId(),
                    $tile->getX(),
                    $tile->getY(),
                    $tile->isFlip()? '1' : '0',
                    $tile->isDisabled()? '1' : '0',
                    $tile->getHowFar(),
                    $tile->getTerrain()? '"'.$tile->getTerrain()->getCode().'"' : 'NULL',
                    'NULL'
                ) :
                sprintf('UPDATE `map` SET `tile_revealed` = %s WHERE `tile_id` = %s', ($tile->isFlip()? '1' : '0'), $tile->getId())
            ;
        }

        return $queries;
    }

    /**
     * Try to get Map from Db, return empty array if not exists
     *
     * @return string
     */
    public static function getMapQry(): string
    {
        return 'SELECT * FROM `map`';
    }
}
