<?php

namespace LdH\Repository;

use LdH\Entity\Map\City;
use LdH\Entity\Map\Resource;
use LdH\Entity\Map\Terrain;
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

    public function flipTile(Tile $tile): void
    {
        $this->query(
            sprintf(
                'UPDATE `%s` SET `tile_revealed` = 1 WHERE tile_id = %s',
                $this->table,
                $tile->getId()
            )
        );
    }

    public function renewResources():void
    {
        $this->query(sprintf('UPDATE `%s` SET `tile_resource1` = 0 WHERE `tile_resource1` = 1', $this->table));
        $this->query(sprintf('UPDATE `%s` SET `tile_resource2` = 0 WHERE `tile_resource2` = 1', $this->table));
        $this->query(sprintf('UPDATE `%s` SET `tile_resource3` = 0 WHERE `tile_resource3` = 1', $this->table));
    }

    public function harvestTileResource(Tile $tile, Resource $resource): bool
    {
        $terrainResources = $tile->getTerrain()->getResources();
        for ($i = 1; $i < 4; $i++) {
            $getter = 'isResource'.$i.'used';
            $used = $tile->$getter();
            if ($terrainResources[$i - 1]->getCode() === $resource->getCode() && !$used) {
                $setter = 'setResource'.$i.'used';
                $tile->$setter(true);
                break;
            }
        }
        if ($i !== 4) {
            $field = 'tile_resource' . $i;
            return $this->query(
                sprintf(
                    'UPDATE `%s` SET `%s` = 1 WHERE tile_id = %s',
                    $this->table,
                    $field,
                    $tile->getId()
                )
            );
        }

        return false;
    }

    public function updateCity(City $city): void
    {
        $this->query(
            sprintf(
                'UPDATE `%s` SET %s WHERE tile_x = 0 AND tile_y = 0',
                $this->table,
                join(', ', self::getDefaultDbTerrainFieldAndValues($city))
            )
        );
    }

    public static function getDefaultDbTerrainFieldAndValues(Terrain $terrain): array
    {
        $fields = ['`tile_terrain` = "'.$terrain->getCode().'"'];

        $resources = $terrain->getResources();
        for ($i = 1; $i <= 3; $i++) {
            $fields[] = '`tile_resource'.$i.'` = '.(array_key_exists($i - 1, $resources) ? '0' : 'NULL');
        }

        return $fields;
    }

    public function getTileInfosByPosition(int $x = 0, int $y = 0): array
    {
        $tileInfos = $this->selectAsObject(
            sprintf('SELECT * FROM `%s` WHERE `tile_x` = %s AND `tile_y` = %s', $this->table, $x, $y)
        );
        return count($tileInfos) === 1 ? $tileInfos[0] : [];
    }

    public function getTileInfosById(int $id): array
    {
        $tileInfos = $this->selectAsObject(
            sprintf('SELECT * FROM `%s` WHERE `tile_id` = %s', $this->table, $id)
        );
        return count($tileInfos) === 1 ? $tileInfos[0] : [];
    }
}
