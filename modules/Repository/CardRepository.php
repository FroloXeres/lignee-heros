<?php

namespace LdH\Repository;

use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Cards\Spell;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
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
                      AND `meeple_status` = '%s'
                      AND tile_terrain IN (%s)
                    GROUP BY m.card_location_arg",
            join(', ', array_map(function(string $code) {return "'$code'";}, $harvesterTypes)),
            Unit::STATUS_FREE,
            join(', ', array_map(function(string $code) {return "'$code'";},$terrainCodes)),
        );
        $units = $this->getObjectListFromDB($qry);

        return array_combine(
            array_map(function(array $unit) {return $unit['card_location_arg'];}, $units),
            array_map(function(array $unit) {return $unit['nb'];}, $units)
        );
    }

    /** @return array<UnitOnMap> */
    public function getFreeFoodHarvestersOnMap(array $terrainCodes, array $harvesterTypes): array
    {
        if ($this->class !== Unit::class) return [];

        $qry = sprintf(
            "SELECT COUNT(m.card_id) as nb, m.card_location_arg, mp.tile_terrain
                    FROM `meeple` m 
                        JOIN `map` mp ON m.card_location = 'map' AND m.card_location_arg = mp.tile_id 
                    WHERE 
                        m.card_type IN (%s) 
                      AND `meeple_status` = '%s'
                      AND tile_terrain IN (%s)
                    GROUP BY m.card_location_arg",
            join(', ', array_map(function(string $code) {return "'$code'";}, $harvesterTypes)),
            Unit::STATUS_FREE,
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
