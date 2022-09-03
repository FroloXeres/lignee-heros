<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Bonus;
use LdH\Entity\Meeple;

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
    public const ELVEN_MAGE        = 517;
    public const ELVEN_SAVANT      = 518;
    public const NANI_WARRIOR      = 519;
    public const NANI_SAVANT       = 520;
    public const HUMANI_WORKER     = 521;
    public const HUMANI_MAGE       = 522;
    public const ORK_WARRIOR       = 523;
    public const ORK_WORKER        = 524;

    public const NEED_INVENTION = 1;
    public const NEED_SPELL     = 2;
    public const NEED_EXPLORE   = 3;
    public const NEED_HARVEST   = 4;
    public const NEED_SURVIVE   = 5;
    public const NEED_WIN_FIGHT = 6;
    public const NEED_UNITS     = 7;

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
    public const NEED_SUB_MAGE         = 13;
    public const NEED_SUB_SAVANT       = 14;
    public const NEED_SUB_WARRIOR      = 15;
    public const NEED_SUB_WORKER       = 16;

    protected int    $need;
    protected int    $subNeed;
    protected int    $needCount;

    /**
     * @param int $code
     */
    public function __construct(int $code, bool $lineage = false)
    {
        $this->setCode($code);
        $this->need      = self::NEED_EXPLORE;
        $this->subNeed   = self::NEED_SUB_ANY;
        $this->needCount = 1;

        // Card specific
        $this->type_arg     = 0;

        $this->location     = $lineage? self::LOCATION_HIDDEN : self::LOCATION_DEFAULT;
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
     */
    public function setCode(string $code): void
    {
        $this->code = self::TYPE_OBJECTIVE . '_' . $code;
        $this->setType($code);
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

    public function getDescription(): string
    {
        $entityAsText = (string) $this;

        return $entityAsText?? parent::getDescription();
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

    /**
     * Return data for Card template build
     *
     * @param Deck $deck
     *
     * @return array
     */
    public function toTpl(Deck $deck): array
    {
        $tpl = parent::toTpl($deck);

        $tpl[self::TPL_ICON] = Deck::TYPE_OBJECTIVE;

        return $tpl;
    }

    /**
     * @param int $subNeed
     *
     * @return string
     */
    protected static function getSubNeedAsText(int $subNeed): string
    {
        switch ($subNeed) {
            case self::NEED_SUB_WORKER: return Meeple::WORKER;
            case self::NEED_SUB_WARRIOR: return Meeple::WARRIOR;
            case self::NEED_SUB_MAGE: return Meeple::MAGE;
            case self::NEED_SUB_SAVANT: return Meeple::SAVANT;
            case self::NEED_SUB_NATURE: return Spell::TYPE_NATURE;
            case self::NEED_SUB_FIGHT: return Spell::TYPE_COMBAT;
            case self::NEED_SUB_SCIENCE: return Bonus::SCIENCE;
            case self::NEED_SUB_FOOD: return Bonus::FOOD;
            case self::NEED_SUB_FAR_I:
            case self::NEED_SUB_FAR_III:
                return 'tile';

            default: return '';
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $txt = [];

        switch ($this->getNeed()) {
            case self::NEED_HARVEST:
                $txt[] = '[end_turn]';
            case self::NEED_UNITS:
            case self::NEED_EXPLORE:
                break;
            case self::NEED_SPELL: $txt[] = '[spell]'; break;
            case self::NEED_INVENTION: $txt[] = '[invention]'; break;
            case self::NEED_WIN_FIGHT: $txt[] = '[fight]'; break;
            default:
                $txt[] = '[none]';
                break;
        }

        if ($this->getSubNeed()) {
            $txt[] = '['.self::getSubNeedAsText($this->getSubNeed()).']';
        }
        if ($this->getNeedCount() > 1) {
            $txt[] = 'x' . $this->getNeedCount();
        }

        return join(' ', $txt);
    }
}
