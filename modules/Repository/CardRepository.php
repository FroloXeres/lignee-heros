<?php

namespace LdH\Repository;

use LdH\Entity\Cards\AbstractCard;

class CardRepository
{
    /**
     * @param string $type
     * @param AbstractCard[] $cards
     * @param string $location
     *
     * @return string
     */
    public static function getCardIdsInLocationQry(string $type, array $cards, string $location = AbstractCard::LOCATION_DEFAULT): string
    {
        if (count($cards)) {
            return sprintf('SELECT `card_id` FROM `%s` WHERE `card_type_arg` IN (%s) AND `card_location` = "%s" LIMIT 1',
                $type,
                join(', ', array_map(function(AbstractCard $card) {
                        return $card->getTypeArg();
                    },
                    $cards
                )),
                $location
            );
        }

        return '';
    }
}
