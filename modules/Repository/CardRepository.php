<?php

namespace LdH\Repository;

use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Cards\Spell;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Object\MapUnit;
use LdH\Object\TileHarvestResources;
use LdH\Object\UnitOnMap;


class CardRepository extends AbstractCardRepository
{
    public function __construct(string $class)
    {
        parent::__construct($class);
    }

    public function getPeopleData(): array
    {
        return $this->selectAll(sprintf(
            'SELECT * FROM `%s` ORDER BY %s',
            $this->table,
            join(', ', $this->getFieldNames(['type', 'location']))
        ));
    }

    /** @return array<MapUnit> */
    public function getFreeUnitPositions(?int $unitId = null): array
    {
        if ($this->class !== Unit::class) return [];

        $qry = sprintf(
            "SELECT `card_id`, `tile_id`, `tile_x`, `tile_y`
                    FROM `meeple` m 
                        JOIN `map` mp ON m.card_location = 'map' AND m.card_location_arg = mp.tile_id
                    WHERE `meeple_status` = '%s'",
            Unit::STATUS_FREE
        );
        if ($unitId !== null) {
            $qry .= ' AND `card_id` = ' . $unitId;
        }
        $units = $this->getObjectListFromDB($qry);

        $freeUnits = [];
        foreach ($units as $unit) {
            $freeUnits[] = new MapUnit(
                (int) $unit['card_id'],
                (int) $unit['tile_id'],
                (int) $unit['tile_x'],
                (int) $unit['tile_y']
            );
        }

        return $freeUnits;
    }

    /** @return array<int, int> */
    public function getScienceHarvestersCount(array $terrainCodes, array $harvesterTypes): array
    {
        if ($this->class !== Unit::class) return [];

        $qry = sprintf(
            "SELECT COUNT(m.card_id) as nb, m.card_location_arg
                    FROM `meeple` m 
                        JOIN `map` mp ON m.card_location = 'map' AND m.card_location_arg = mp.tile_id 
                    WHERE 
                        m.card_type IN (%s) 
                      AND `meeple_status` <> '%s'
                      AND tile_terrain IN (%s)
                    GROUP BY m.card_location_arg",
            join(', ', array_map(function(string $code) {return "'$code'";}, $harvesterTypes)),
            Unit::STATUS_ACTED,
            join(', ', array_map(function(string $code) {return "'$code'";},$terrainCodes)),
        );
        $units = $this->getObjectListFromDB($qry);

        return array_combine(
            array_map(function(array $unit) {return $unit['card_location_arg'];}, $units),
            array_map(function(array $unit) {return $unit['nb'];}, $units)
        );
    }

    /** @return array<UnitOnMap> */
    public function getUnitsOnMapByTypeAndNotStatus(array $terrainCodes, array $unitTypes, string $status): array
    {
        if ($this->class !== Unit::class) return [];

        $qry = sprintf(
            "SELECT COUNT(m.card_id) as nb, m.card_location_arg, mp.tile_terrain
                    FROM `meeple` m 
                        JOIN `map` mp ON m.card_location = 'map' AND m.card_location_arg = mp.tile_id 
                    WHERE 
                        m.card_type IN (%s) 
                      AND `meeple_status` <> '%s'
                      AND tile_terrain IN (%s)
                    GROUP BY m.card_location_arg",
            join(', ', array_map(function(string $code) {return "'$code'";}, $unitTypes)),
            $status,
            join(', ', array_map(function(string $code) {return "'$code'";}, $terrainCodes)),
        );
        $units = $this->getObjectListFromDB($qry);

        return array_combine(
            array_map(function(array $unit) {return $unit['card_location_arg'];}, $units),
            array_map(function(array $unit) {
                return new UnitOnMap(
                    (int) $unit['card_location_arg'],
                    (int) $unit['nb'],
                    $unit['tile_terrain']
                );
            }, $units)
        );
    }

    /** @return array<TileHarvestResources> */
    public function getHarvestableResourcesAndUnits(): array
    {
        if ($this->class !== Unit::class) return [];

        $tiles = [];
        $qry = sprintf(
            "SELECT m.`card_id`, mp.`tile_id`, mp.`tile_terrain`, mp.`tile_resource1`, mp.`tile_resource2`, mp.`tile_resource3`
                    FROM `%s` m 
                        JOIN `map` mp ON m.card_location = 'map' AND m.card_location_arg = mp.tile_id 
                    WHERE 
                        m.card_type IN (%s) 
                      AND `meeple_status` <> '%s'
                      AND (`tile_resource1` = 0 OR `tile_resource2` = 0 OR `tile_resource3` = 0)",
            $this->table,
            join(', ', array_map(function(string $code) {return "'$code'";}, Meeple::HARVESTERS)),
            Unit::STATUS_ACTED,
        );
        $data = $this->getObjectListFromDB($qry);
        foreach ($data as $line) {
            $tileId = (int) $line['tile_id'];
            if (!array_key_exists($tileId, $tiles)) {
                $tiles[$tileId] = new TileHarvestResources();
                $tiles[$tileId]->tileId = $tileId;
                $tiles[$tileId]->terrain = $line['tile_terrain'];
                for ($i = 0; $i < 2; $i++) {
                    $tileResourceKey = 'tile_resource' . ($i + 1);
                    $tiles[$tileId]->resources[$i] = $line[$tileResourceKey] === null ? null : ($line[$tileResourceKey] === '1');
                }
            }
            $tiles[$tileId]->harvesters[] = (int) $line['card_id'];
        }

        return $tiles;
    }

    public function disableAllCards(): bool
    {
        if (!in_array($this->class, [Invention::class, Spell::class], true)) return false;

        return $this->query(sprintf(
            "UPDATE %s SET `card_activated` = 0 WHERE 1",
            $this->table
        ));
    }

    public function killUnit(Unit $unit): void
    {
        $this->query(sprintf(
            "DELETE FROM %s WHERE `card_id` = %s",
            $this->table,
            $unit->getId()
        ));
    }

    public function setAllUnitsToStatus(string $status): bool
    {
        if ($this->class !== Unit::class) return false;

        return $this->query(sprintf(
            "UPDATE %s SET `meeple_status` = '%s' WHERE 1",
            $this->table,
            $status
        ));
    }

    /** @return array<'byUser': array<int, int>, 'byType': int[]> */
    public function getCompletedObjectives(): array
    {
        if ($this->class !== Objective::class) return [];

        return $this->getObjectListFromDB(
            sprintf(
                "SELECT `card_type` as objectiveType, `card_location_arg` as playerId FROM `objective` WHERE `card_location` = '%s' AND `card_completed` = 1",
                BoardCardInterface::LOCATION_HAND
            )
        );
    }
}
