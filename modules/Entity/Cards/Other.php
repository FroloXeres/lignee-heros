<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Bonus;

class Other extends AbstractCard
{
    public const ABANDONED_GEM_MINE   = 401;
    public const ABANDONED_METAL_MINE = 402;
    public const RUINED_COTTAGE       = 403;
    public const TELLURIC_CROSSING    = 404;
    public const HERMIT_WEAPON_MASTER = 405;
    public const WISH_FOUNTAIN        = 406;
    public const LOST_LIBRARY         = 407;
    public const FIRE                 = 408;
    public const EXPLOSIVE_ARTEFACT   = 409;
    public const DAMAGED_FRUITS       = 410;
    public const DROUGHT              = 411;
    public const FLOOD                = 412;
    public const LANDSLIDE            = 413;
    public const INHABITED_CAVE       = 414;
    public const CURSE                = 415;

    /**
     * @var Bonus[]
     */
    protected array $gives;

    /**
     * @param int $code
     */
    public function __construct(int $code)
    {
        $this->setCode($code);

        // Card specific
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
     */
    public function setCode(string $code): void
    {
        $this->code = self::TYPE_OTHER . '_' . $code;
        $this->setType($code);
    }

    /**
     * @return array
     */
    public function getGives(): array
    {
        return $this->gives;
    }

    /**
     * @param Bonus $give
     *
     * @return Other
     */
    public function addGive(Bonus $give): Other
    {
        $this->gives[] = $give;

        return $this;
    }

    /**
     * @param Bonus[] $gives
     *
     * @return Other
     */
    public function setGives(array $gives): Other
    {
        $this->gives = $gives;

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
            'nbr'          => 1
        ];
    }

    /**
     * Return data for Card template build
     *
     * @param string $deck
     *
     * @return array
     */
    public function toTpl(string $deck): array
    {
        $tpl = parent::toTpl($deck);

        $tpl[self::TPL_GAIN] = join(' ', $this->getGives());

        return $tpl;
    }
}
