<?php

namespace LdH\Repository;

use LdH\Entity\Meeple;
use LdH\Entity\Unit;


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
    public function getScienceHarvesters(array $terrainCodes): array
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
            join(', ', array_map(function(string $code) {return "'$code'";},[Meeple::SAVANT, Meeple::NANI_SAVANT, Meeple::ELVEN_SAVANT])),
            Unit::STATUS_FREE,
            join(', ', array_map(function(string $code) {return "'$code'";},$terrainCodes)),
        );
        $units = $this->getObjectListFromDB($qry);

        return array_combine(
            array_map(function(array $unit) {return $unit['card_location_arg'];}, $units),
            array_map(function(array $unit) {return $unit['nb'];}, $units)
        );
    }

    public function getFoodHarvesters(array $countByTerrains): array
    {
        return [];
    }
}
