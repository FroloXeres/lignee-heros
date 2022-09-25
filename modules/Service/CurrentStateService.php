<?php

namespace LdH\Service;

class CurrentStateService
{
    public const GLB_TURN_LFT    = 'turnLeft';
    public const GLB_PEOPLE_CNT  = 'peopleCount';
    public const GLB_WORKER_CNT  = 'workerCount';
    public const GLB_WARRIOR_CNT = 'warriorCount';
    public const GLB_SAVANT_CNT  = 'savantCount';
    public const GLB_MAGE_CNT    = 'mageCount';
    public const GLB_FOOD_PRD    = 'foodProduction';
    public const GLB_FOOD_STK    = 'foodStock';
    public const GLB_SCIENCE_PRD = 'scienceProduction';
    public const GLB_SCIENCE_STK = 'scienceStock';
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

    public const CURRENT_STATES = [
        CurrentStateService::GLB_TURN_LFT    => 10,
        CurrentStateService::GLB_PEOPLE_CNT  => 11,
        CurrentStateService::GLB_WORKER_CNT  => 20,
        CurrentStateService::GLB_WARRIOR_CNT => 21,
        CurrentStateService::GLB_SAVANT_CNT  => 22,
        CurrentStateService::GLB_MAGE_CNT    => 23,
        CurrentStateService::GLB_FOOD_PRD    => 12,
        CurrentStateService::GLB_FOOD_STK    => 13,
        CurrentStateService::GLB_SCIENCE_PRD => 14,
        CurrentStateService::GLB_SCIENCE_STK => 15,
        CurrentStateService::GLB_WOOD_STK    => 24,
        CurrentStateService::GLB_ANIMAL_STK  => 25,
        CurrentStateService::GLB_GEM_STK     => 26,
        CurrentStateService::GLB_PAPER_STK   => 27,
        CurrentStateService::GLB_METAL_STK   => 28,
        CurrentStateService::GLB_STONE_STK   => 29,
        CurrentStateService::GLB_CLAY_STK    => 30,
        CurrentStateService::GLB_MEDIC_STK   => 31,
        CurrentStateService::GLB_LIFE        => 16,
        CurrentStateService::GLB_WAR_PWR     => 17,
        CurrentStateService::GLB_WAR_DFS     => 18,
        CurrentStateService::GLB_CTY_DFS     => 19
    ];
}