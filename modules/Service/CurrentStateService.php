<?php

namespace LdH\Service;

use LdH\Entity\Meeple;

class CurrentStateService
{
    public const GLB_TURN_LFT    = 'turnLeft';
    public const GLB_PEOPLE_CNT  = 'peopleCount';
    public const GLB_WORKER_CNT  = 'workerCount';
    public const GLB_WARRIOR_CNT = 'warriorCount';
    public const GLB_SAVANT_CNT  = 'savantCount';
    public const GLB_MAGE_CNT    = 'mageCount';
    public const GLB_FOOD_PRD    = 'foodProduction';
    public const GLB_FOOD        = 'food';
    public const GLB_FOOD_STK    = 'foodStock';
    public const GLB_SCIENCE_PRD = 'scienceProduction';
    public const GLB_SCIENCE     = 'science';
    public const GLB_WOOD_STK    = 'woodStock';
    public const GLB_ANIMAL_STK  = 'animalStock';
    public const GLB_GEM_STK     = 'gemStock';
    public const GLB_PAPER_STK   = 'paperStock';
    public const GLB_METAL_STK   = 'metalStock';
    public const GLB_STONE_STK   = 'stoneStock';
    public const GLB_CLAY_STK    = 'clayStock';
    public const GLB_MEDIC_STK   = 'medicStock';
    public const GLB_LIFE        = 'life';
    public const GLB_WAR_PWR     = 'warriorPower';
    public const GLB_WAR_DFS     = 'warriorDefense';
    public const GLB_CTY_DFS     = 'cityDefense';

    public const LAST_TURN    = 50;
    public const START_PEOPLE = 10;
    public const START_FOOD_PRD = 2;
    public const START_SCIENCE_PRD = 1;
    public const START_LIFE = 1;
    public const START_WAR_PWR = 1;
    public const START_CTY_DFS = 1;

    public const CURRENT_STATES = [
        self::GLB_TURN_LFT    => 10,
        self::GLB_PEOPLE_CNT  => 11,
        self::GLB_WORKER_CNT  => 20,
        self::GLB_WARRIOR_CNT => 21,
        self::GLB_SAVANT_CNT  => 22,
        self::GLB_MAGE_CNT    => 23,
        self::GLB_FOOD_PRD    => 12,
        self::GLB_FOOD        => 32,
        self::GLB_FOOD_STK    => 13,
        self::GLB_SCIENCE_PRD => 14,
        self::GLB_SCIENCE     => 33,
        self::GLB_WOOD_STK    => 24,
        self::GLB_ANIMAL_STK  => 25,
        self::GLB_GEM_STK     => 26,
        self::GLB_PAPER_STK   => 27,
        self::GLB_METAL_STK   => 28,
        self::GLB_STONE_STK   => 29,
        self::GLB_CLAY_STK    => 30,
        self::GLB_MEDIC_STK   => 31,
        self::GLB_LIFE        => 16,
        self::GLB_WAR_PWR     => 17,
        self::GLB_WAR_DFS     => 18,
        self::GLB_CTY_DFS     => 19
    ];

    public static function getStateByMeepleType(Meeple $meeple): string
    {
        switch ($meeple->getCode()) {
            case Meeple::ORK_WORKER:
            case Meeple::HUMANI_WORKER:
            case Meeple::WORKER:
                return self::GLB_WORKER_CNT;
            case Meeple::ORK_WARRIOR:
            case Meeple::NANI_WARRIOR:
            case Meeple::WARRIOR:
                return self::GLB_WARRIOR_CNT;
            case Meeple::ELVEN_MAGE:
            case Meeple::HUMANI_MAGE:
            case Meeple::MAGE:
                return self::GLB_MAGE_CNT;
            case Meeple::ELVEN_SAVANT:
            case Meeple::NANI_SAVANT:
            case Meeple::SAVANT:
                return self::GLB_SAVANT_CNT;
            default: return '';
        }
    }
}