<?php

namespace LdH\Service;

class CardService
{
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
}
