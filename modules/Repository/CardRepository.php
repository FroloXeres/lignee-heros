<?php

namespace LdH\Repository;

use LdH\Entity\Cards\AbstractCard;

class CardRepository extends AbstractRepository
{
    /**
     * @param string  $type
     * @param int[]   $typeArgs
     * @param ?string $location
     * @param bool    $oneOfEach
     *
     * @return array
     */
    public function getCardIds(string $type, array $typeArgs, string $location = null, bool $oneOfEach = false): array
    {
        if (!count($typeArgs)) {
            return [];
        }

        return array_map(function(array $card) {
                return $card['card_type_arg'];
            },
            $this->selectAll(
                sprintf('SELECT `card_id`, `card_type_arg` FROM `%s` WHERE `card_type_arg` IN (%s)%s',
                    $type,
                    join(', ', $typeArgs),
                    ($location ?
                        sprintf(' AND `card_location` = "%s"%s',
                            $location,
                            ($oneOfEach ? ' GROUP BY `card_type_arg`, `card_id`' : '')
                        ) :
                        ''
                    )
                )
            )?? []
        );
    }

    public function getCardTypeDataByLocation(string $type, string $location = null): array
    {
        return $this->selectAll(
            sprintf('SELECT * FROM `%s`%s',
                $type,
                $location !== null ? sprintf(
                    ' WHERE `card_location` = "%s"',
                    $location
                ) : ''
            )
        );
    }

    public function getPeopleData(): array
    {
        return $this->selectAll('SELECT * FROM `meeple` ORDER BY `card_type`, `card_location`');
    }
}
