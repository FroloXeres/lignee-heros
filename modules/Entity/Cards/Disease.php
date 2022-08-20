<?php

namespace LdH\Entity\Cards;

class Disease extends AbstractCard
{
    public const LEVEL_1 = 1;
    public const LEVEL_2 = 2;
    public const LEVEL_3 = 3;

    public const NO_WIZARD        = 301;
    public const ACTED_ZONE       = 302;
    public const ACTED_MOVED_HEAL = 303;
    public const DEAD             = 304;
    public const ACTED_HEAL       = 305;
    public const ACTED_MOVED      = 306;

    protected int $code;
    protected int $level;

    /**
     * @param int $code
     */
    public function __construct(int $level, int $code)
    {
        $this->code  = $code;
        $this->level = $level;

        // Card specific
        $this->type         = $this->level;
        $this->type_arg     = $this->code;
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
     * @return Disease
     */
    public function setCode(string $code): Disease
    {
        $this->code = $code;
        $this->setTypeArg($code);

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return Disease
     */
    public function setLevel(int $level): Disease
    {
        $this->level = $level;
        $this->setType((string) $level);

        return $this;
    }

    /**
     * What to do on explore
     *
     * @param ?int $playerId
     *
     * @return void
     */
    public function occur(?int $playerId)
    {

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
}
