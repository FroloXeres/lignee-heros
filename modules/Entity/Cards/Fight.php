<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Bonus;

class Fight extends AbstractCard
{
    public const PIC_BULL      = 201;
    public const CRAWLING      = 202;
    public const LIZARDS       = 203;
    public const SAND_WORM     = 204;
    public const LICH          = 205;
    public const WOLFS         = 206;
    public const BASILISK      = 207;
    public const ELEMENTAL     = 208;
    public const UNDEAD        = 209;
    public const VAMPIRES      = 210;
    public const GIANT_SPIDERS = 211;
    public const GIANTS        = 212;
    public const SAND_SPIRIT   = 213;
    public const BRIGANDS      = 214;
    public const SORCERER      = 215;
    public const CENTAURS      = 216;

    protected int  $code;
    protected bool $toCity = false;
    protected int  $power;

    /**
     * @var Bonus[]
     */
    protected array $gives;

    /**
     * @param int  $code
     * @param int  $power
     * @param bool $toCity
     */
    public function __construct(int $code, int $power, bool $toCity = false)
    {
        $this->setCode($code);
        $this->power  = $power;
        $this->toCity = $toCity;

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
     *
     * @return Fight
     */
    public function setCode(string $code): Fight
    {
        $this->code = self::TYPE_FIGHT . '_' . $code;
        $this->setType($code);

        return $this;
    }

    /**
     * @return bool
     */
    public function toCity(): bool
    {
        return $this->toCity;
    }

    /**
     * @param bool $toCity
     *
     * @return Fight
     */
    public function setToCity(bool $toCity): Fight
    {
        $this->toCity = $toCity;

        return $this;
    }

    /**
     * @return int
     */
    public function getPower(): int
    {
        return $this->power;
    }

    /**
     * @param int $power
     *
     * @return Fight
     */
    public function setPower(int $power): Fight
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return Bonus[]
     */
    public function getGives(): array
    {
        return $this->gives;
    }

    /**
     * @param Bonus $give
     *
     * @return Fight
     */
    public function addGive(Bonus $give): Fight
    {
        $this->gives[] = $give;

        return $this;
    }

    /**
     * @param Bonus[] $gives
     *
     * @return Fight
     */
    public function setGives(array $gives): Fight
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

        $tpl[self::TPL_ICON] = self::TYPE_FIGHT;
        $tpl[self::TPL_COST] = $this->getPower();
        $tpl[self::TPL_GAIN] = join( ' ', $this->getGives());
        $tpl[self::TPL_TEXT_BOLD] = $this->toCity()? clienttranslate('City raid') : null;

        return $tpl;
    }
}
