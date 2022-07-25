<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Meeple;
use LdH\Entity\Bonus;

class Explore extends AbstractCard
{
    public const DECK_SIZE    = 36;

    public const TYPE_BATTLE  = 'battle';
    public const TYPE_DISEASE = 'disease';
    public const TYPE_OTHER   = 'other';

    public const DISEASE_NO_WIZARD = 1;
    public const DISEASE_ACT_DONE  = 2;

    protected string $code  = '';

    /**
     * @var Bonus[]
     */
    protected array  $gives = [];

    /**
     * @param string $code
     * @param int    $subType
     */
    public function __construct(string $code, int $subType)
    {
        $this->code = $code;

        // Card specific
        $this->type         = $this->code;
        $this->type_arg     = $subType;
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
     * @return Explore
     */
    public function setCode(string $code): Explore
    {
        $this->code = $code;
        $this->setType($code);

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
     * @param Bonus $bonus
     *
     * @return Explore
     */
    public function addGive(Bonus $bonus): Explore
    {
        $this->gives[] = $bonus;

        return $this;
    }

    /**
     * @param Bonus[] $gives
     */
    public function setGives(array $gives): void
    {
        $this->gives = $gives;
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
