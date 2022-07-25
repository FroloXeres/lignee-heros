<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Meeple;
use LdH\Entity\Bonus;

class EndTurn extends AbstractCard
{
    public const DECK_SIZE    = 50;

    public const END_FLOOD = 'flood';

    protected string $code  = '';

    /**
     * @var Bonus[]
     */
    protected array  $gives = [];

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;

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
