<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Meeple;
use LdH\Entity\Bonus;

class Invention extends AbstractCard
{
    public const SMITHING = 'smithing';

    protected string $code  = '';

    // Cost !


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
     * @return Invention
     */
    public function setCode(string $code): Invention
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
     * @return Invention
     */
    public function addGive(Bonus $bonus): Invention
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
