<?php

namespace LdH\Service;

class MessageHelper
{
    const ITEM_SEPARATOR = ', ';
    const LAST_ITEM_SEPARATOR = ' and ';

    public static function formatList(
        array $items,
        string $separator = self::ITEM_SEPARATOR,
        string $lastSeparator = self::LAST_ITEM_SEPARATOR
    ): string {
        if (empty($items)) {
            return clienttranslate('none');
        }
        if (count($items) < 2) {
            return join('', $items);
        }

        $lastItem = array_pop($items);
        return join(
            clienttranslate($lastSeparator),
            [
                join(
                    clienttranslate($separator),
                    $items
                ),
                $lastItem
            ]
        );
    }
}