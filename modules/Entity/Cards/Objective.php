<?php

namespace LdH\Entity\Cards;

class Objective extends AbstractCard
{
    public const ABUNDANCE         = 501;
    public const DA_VINCI          = 502;
    public const ARCHMAGE          = 503;
    public const CAUTIOUS_EXPLORER = 504;
    public const TO_WORLD_ENDING   = 505;
    public const ARTEFACT_LOVER    = 506;
    public const IN_WOLF_MOUTH     = 507;
    public const ARCHAEOLOGIST     = 508;
    public const WE_NEED_ALL       = 509;
    public const IN_CASE_OF        = 510;
    public const SURVIVOR          = 511;
    public const NOT_EVEN_HURT     = 512;
    public const WARMONGER         = 513;
    public const RESEARCHER        = 514;
    public const MAGISTER          = 515;
    public const ESTINY_CHILD      = 516;

    public const NEED_INVENTION = 1;
    public const NEED_SPELL     = 2;
    public const NEED_EXPLORE   = 3;
    public const NEED_HARVEST   = 4;
    public const NEED_SURVIVE   = 5;
    public const NEED_WIN_FIGHT = 6;

    public const NEED_SUB_ANY          = 0;
    public const NEED_SUB_FIGHT        = 1;
    public const NEED_SUB_SCIENCE      = 2;
    public const NEED_SUB_NATURE       = 3;
    public const NEED_SUB_FOOD         = 4;
    public const NEED_SUB_FAR_I        = 5;
    public const NEED_SUB_FAR_III      = 6;
    public const NEED_SUB_TOWER        = 7;
    public const NEED_SUB_LAIR         = 8;
    public const NEED_SUB_RUINS        = 9;
    public const NEED_SUB_RESOURCE_ALL = 10;
    public const NEED_SUB_RESOURCE_ONE = 11;
    public const NEED_SUB_NO_WOUND     = 12;

    protected int $code;
    protected int $need;
    protected int $subNeed;
    protected int $needCount;

    /**
     * @param int $code
     */
    public function __construct(int $code)
    {
        $this->code      = $code;
        $this->need      = self::NEED_EXPLORE;
        $this->subNeed   = self::NEED_SUB_ANY;
        $this->needCount = 1;

        // Card specific
        $this->type         = $this->code;
        $this->type_arg     = 0;
        $this->location_arg = 0;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Objective
     */
    public function setCode(string $code): Objective
    {
        $this->code = $code;
        $this->setType($code);

        return $this;
    }

    /**
     * @return int
     */
    public function getNeed(): int
    {
        return $this->need;
    }

    /**
     * @param int $need
     *
     * @return Objective
     */
    public function setNeed(int $need): Objective
    {
        $this->need = $need;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubNeed(): int
    {
        return $this->subNeed;
    }

    /**
     * @param int $subNeed
     *
     * @return Objective
     */
    public function setSubNeed(int $subNeed): Objective
    {
        $this->subNeed = $subNeed;

        return $this;
    }

    /**
     * @return int
     */
    public function getNeedCount(): int
    {
        return $this->needCount;
    }

    /**
     * @param int $needCount
     *
     * @return Objective
     */
    public function setNeedCount(int $needCount): Objective
    {
        $this->needCount = $needCount;

        return $this;
    }

    /**
     * Return data for Card module
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'         => $this->getType(),
            'type_arg'     => $this->getTypeArg(),
            'nbr'          => 1,
            'need'         => $this->getNeed(),
            'sub'          => $this->getSubNeed(),
            'count'        => $this->getNeedCount()
        ];
    }
}
