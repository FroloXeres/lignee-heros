<?php

namespace LdH\Entity;

class Lineage extends AbstractCard
{
    protected string  $code        = '';
    protected ?Meeple $meeple      = null;

    protected ?Bonus $meeplePower    = null;
    protected ?Bonus $objectiveBonus = null;

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
     * @return Lineage
     */
    public function setCode(string $code): Lineage
    {
        $this->code = $code;
        $this->setType($code);

        return $this;
    }

    /**
     * @return Meeple|null
     */
    public function getMeeple(): ?Meeple
    {
        return $this->meeple;
    }

    /**
     * @param Meeple|null $meeple
     *
     * @return Lineage
     */
    public function setMeeple(?Meeple $meeple): Lineage
    {
        $this->meeple = $meeple;

        if (!$meeple->getLineage()) {
            $meeple->setLineage($this);
        }

        return $this;
    }

    /**
     * @return Bonus
     */
    public function getMeeplePower(): Bonus
    {
        return $this->meeplePower;
    }

    /**
     * @param Bonus $meeplePower
     *
     * @return Lineage
     */
    public function setMeeplePower(Bonus $meeplePower): Lineage
    {
        $this->meeplePower = $meeplePower;

        return $this;
    }

    /**
     * @return Bonus
     */
    public function getObjectiveBonus(): Bonus
    {
        return $this->objectiveBonus;
    }

    /**
     * @param Bonus $objectiveBonus
     *
     * @return Lineage
     */
    public function setObjectiveBonus(Bonus $objectiveBonus): Lineage
    {
        $this->objectiveBonus = $objectiveBonus;

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
}
