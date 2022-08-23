<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Meeple;
use LdH\Entity\Bonus;

class Lineage extends AbstractCard
{
    public const LEADING_TYPE_EVERY3TURN = 1;
    public const LEADING_TYPE_FIGHT      = 2;

    protected string  $code        = '';
    protected ?Meeple $meeple      = null;

    protected ?Bonus     $meeplePower    = null;
    protected ?Objective $objective      = null;
    protected ?Bonus     $objectiveBonus = null;
    protected int        $leadingType    = self::LEADING_TYPE_EVERY3TURN;
    protected ?Bonus     $leadingBonus   = null;

    /**
     * @param string $code
     */
    public function __construct(string $code)
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
     *
     * @return Lineage
     */
    public function setCode(string $code): Lineage
    {
        $this->setId(self::TYPE_LINEAGE . '_' . $code);

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
     * @return Objective|null
     */
    public function getObjective(): ?Objective
    {
        return $this->objective;
    }

    /**
     * @param Objective $objective
     *
     * @return Lineage
     */
    public function setObjective(Objective $objective): Lineage
    {
        $this->objective = $objective;

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
     * @return int
     */
    public function getLeadingType(): int
    {
        return $this->leadingType;
    }

    /**
     * @param int $leadingType
     *
     * @return Lineage
     */
    public function setLeadingType(int $leadingType): Lineage
    {
        $this->leadingType = $leadingType;

        return $this;
    }

    /**
     * @return Bonus|null
     */
    public function getLeadingBonus(): ?Bonus
    {
        return $this->leadingBonus;
    }

    /**
     * @param Bonus|null $leadingBonus
     *
     * @return Lineage
     */
    public function setLeadingBonus(?Bonus $leadingBonus): Lineage
    {
        $this->leadingBonus = $leadingBonus;

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

        $tpl[self::TPL_ICON]            = $this->getMeeple()->getCode();
        $tpl[self::TPL_MEEPLE_POWER]    = (string)$this->getMeeplePower();
        $tpl[self::TPL_OBJECTIVE]       = (string) $this->getObjective();
        $tpl[self::TPL_OBJECTIVE_BONUS] = (string)$this->getObjectiveBonus();
        $tpl[self::TPL_LEAD_TYPE]       = $this->getLeadingType() === self::LEADING_TYPE_EVERY3TURN ? 'end_turn' : 'fight';
        $tpl[self::TPL_LEAD_POWER]      = (string)$this->getLeadingBonus();

        return $tpl;
    }
}
