<?php

namespace LdH\Service;

use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\Tile;

class MapService
{
    public const DEFAULT_RADIUS = 3;

    /**
     * Hexa, $radius circles around central town, flipped on start
     * 2 * $radius + 1 square tiles, with some disabled (too far from center)
     *
     * Dependant of CSS (have to be parametrized to change radius)
     *
     * @param int $radius
     *
     * @return Tile[]
     */
    public static function generateMap(int $radius = self::DEFAULT_RADIUS): array
    {
        $tiles       = [];
        $width       = $radius * 2 + 1;
        $max         = $width * $width + 1;
        $limit       = (int) floor($width / 2);
        $x           = -$limit;
        $yStart      = -1;
        $y           = $yStart;
        $line        = 1;
        for ($id = 1; $id < $max; $id++) {
            if ($x > $limit) {
                $x = -$limit;
                $yStart++;
                $y = $yStart;
                $line++;
            }
            if ($y < $yStart - $limit) {$y = -1;}

            $distance = max(abs($y), abs($x), abs(-$x - $y));
            $disabled = $distance > $limit;
            $flip     = !$x && !$y;     // Center is town (visible at the beginning)
            $tile     = new Tile($id, $x, $y, $distance, $disabled, $flip);

            $tiles[$tile->getId()] = $tile;

            $x++;
            if ($line%2 !== 0? ($id%2 !== 0) : ($id%2 === 0)) $y--;
        }

        return $tiles;
    }

    /**
     * @param array $defaultMap
     * @param array $terrains
     *
     * @return array
     */
    public static function initMap(array $defaultMap, array $terrains = []): array
    {
        if (!empty($terrains)) {
            $defaultMap = self::randomTerrain($defaultMap, $terrains);
        }

        return $defaultMap;
    }

    /**
     * @param array $dbLines
     * @param array $terrains
     *
     * @return array
     */
    public static function buildMapFromDb(array $dbLines, array $terrains = []): array
    {
        $tiles       = [];
        $fillTerrain = !empty($terrains);

        foreach ($dbLines as $id => $line) {
            $tile = new Tile(
                (int) $id,
                (int) $line['tile_x'],
                (int) $line['tile_y'],
                (int) $line['tile_far'],
                $line['tile_disabled'] === '1',
                $line['tile_revealed'] === '1'
            );

            if ($fillTerrain) {
                $tile->setTerrain($terrains[$line['tile_terrain']]?? null);
            }

            $tiles[] = $tile;
        }

        return $tiles;
    }

    /**
     * Choose CSS class to display hidden tile, revealed one and display image if it does
     *
     * @param Tile $tile
     *
     * @return string
     */
    public static function getClass(Tile $tile): string
    {
        return sprintf(
            'tile%s',
            $tile->isDisabled()? ' tile_disabled' : ''
        );
    }

    /**
     * To convert distance to the center in roman numeral
     *
     * @param Tile $tile
     *
     * @return string
     */
    public static function getDistanceToDisplay(Tile $tile): string
    {
        $howFar = [0 => '', 1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];

        return $howFar[$tile->getHowFar()]?? '';
    }

    /**
     * @param Tile $tileFrom
     * @param Tile $tileTo
     *
     * @return array
     */
    public static function getPath(Tile $tileFrom, Tile $tileTo): array
    {
        return [];
    }

    /**
     * @param Tile $tileFrom
     * @param Tile $tileTo
     *
     * @return int
     */
    public static function getDistance(Tile $tileFrom, Tile $tileTo): int
    {
        return 0;
    }

    /**
     * Generate random terrains for given tile map
     *
     * @param Tile[]    $tiles
     * @param Terrain[] $terrains
     *
     * @return array
     */
    public static function randomTerrain(array $tiles, array $terrains): array
    {
        $terrainByDistance = self::getTerrainByDistance();
        $keys              = array_fill(0, count($terrainByDistance), 0);
        foreach ($tiles as $tile) {
            if (!$tile->isDisabled()) {
                $key   = $keys[$tile->getHowFar()]++;
                $code  = $terrainByDistance[$tile->getHowFar()][$key];
                $tile->setTerrain($terrains[$code]);
            }
        }

        return $tiles;
    }

    /**
     * For each distance, prepare terrain for tiles
     *
     * @return array[]
     */
    private static function getTerrainByDistance(): array
    {
        // 6 tiles at level 1
        // 12 tiles at level 2
        // 18 tiles at level 3
        $terrainByDistance = [
            0 => [Terrain::TOWN_HUMANIS],
            1 => [
                Terrain::PLAIN, Terrain::PLAIN_LAKE, Terrain::PLAIN_WOOD,
                Terrain::HILL, Terrain::MOUNTAIN_WOOD, Terrain::FOREST
            ],
            2 => [
                Terrain::PLAIN, Terrain::PLAIN_DESERT, Terrain::HILL_PLATEAU,
                Terrain::HILL_WOOD_RIVER, Terrain::SWAMP, Terrain::SWAMP_LAIR,
                Terrain::DESERT, Terrain::MOUNTAIN_LAIR, Terrain::MOUNTAIN_LAKE,
                Terrain::MOUNTAIN_WOOD, Terrain::FOREST_DENSE, Terrain::FOREST_RUIN
            ],
            3 => [
                Terrain::PLAIN, Terrain::PLAIN_WOOD, Terrain::PLAIN_RIVER_RUIN,
                Terrain::HILL_RUIN, Terrain::HILL_LAKE, Terrain::SWAMP,
                Terrain::SWAMP, Terrain::SWAMP_TOWER, Terrain::DESERT,
                Terrain::DESERT_STONE, Terrain::DESERT_STONE, Terrain::MOUNTAIN,
                Terrain::HILL_WOOD_LAIR, Terrain::MOUNTAIN_TOWER, Terrain::MOUNTAIN_RIVER,
                Terrain::FOREST_TOWER, Terrain::FOREST_LAIR, Terrain::FOREST_DENSE
            ]
        ];
        shuffle($terrainByDistance[1]);
        shuffle($terrainByDistance[2]);
        shuffle($terrainByDistance[3]);

        return $terrainByDistance;
    }
}
