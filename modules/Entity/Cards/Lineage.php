<?php

namespace LdH\Entity\Cards;

use LdH\Entity\Meeple;
use LdH\Entity\Bonus;

class Lineage extends AbstractCard
{
    public const LEADING_TYPE_EVERY3TURN = 1;
    public const LEADING_TYPE_FIGHT      = 2;

    protected ?Meeple $meeple      = null;

    protected ?Bonus     $meeplePower    = null;
    protected ?Objective $objective      = null;
    protected ?Bonus     $objectiveBonus = null;
    protected int        $leadingType    = self::LEADING_TYPE_EVERY3TURN;
    protected ?Bonus     $leadingBonus   = null;

    protected bool $objectiveCompleted = false;
    protected bool $leader = false;

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

    private static function buildCode(string $id): string
    {
        return self::TYPE_LINEAGE . '_' . $id;
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
        $this->code = self::buildCode($code);
        $this->setType($code);
    }

    public function getMeeple(): ?Meeple
    {
        return $this->meeple;
    }

    public function setMeeple(?Meeple $meeple): self
    {
        $this->meeple = $meeple;

        if (!$meeple->getLineage()) {
            $meeple->setLineage($this);
        }

        return $this;
    }

    public function getMeeplePower(): Bonus
    {
        return $this->meeplePower;
    }

    public function setMeeplePower(Bonus $meeplePower): self
    {
        $this->meeplePower = $meeplePower;

        return $this;
    }

    public function getObjective(): ?Objective
    {
        return $this->objective;
    }

    public function setObjective(Objective $objective): self
    {
        $this->objective = $objective;

        return $this;
    }

    public function getObjectiveBonus(): Bonus
    {
        return $this->objectiveBonus;
    }

    public function setObjectiveBonus(Bonus $objectiveBonus): self
    {
        $this->objectiveBonus = $objectiveBonus;

        return $this;
    }

    public function getLeadingType(): int
    {
        return $this->leadingType;
    }

    public function setLeadingType(int $leadingType): self
    {
        $this->leadingType = $leadingType;

        return $this;
    }

    public function getLeadingBonus(): ?Bonus
    {
        return $this->leadingBonus;
    }

    public function setLeadingBonus(?Bonus $leadingBonus): self
    {
        $this->leadingBonus = $leadingBonus;

        return $this;
    }

    public function isObjectiveCompleted(): bool
    {
        return $this->objectiveCompleted;
    }

    public function setObjectiveCompleted(bool $objectiveCompleted): self
    {
        $this->objectiveCompleted = $objectiveCompleted;

        return $this;
    }

    public function isLeader(): bool
    {
        return $this->leader;
    }

    public function setLeader(bool $leader): self
    {
        $this->leader = $leader;

        return $this;
    }

    /** Return data for Card module */
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
     * @param Deck $deck
     *
     * @return array
     */
    public function toTpl(Deck $deck): array
    {
        $tpl = parent::toTpl($deck);

        $tpl[self::TPL_ICON]            = 'lineage';
        $tpl[self::TPL_MEEPLE]          = $this->getMeeple()->getCode();
        $tpl[self::TPL_MEEPLE_POWER]    = (string) $this->getMeeplePower();
        $tpl[self::TPL_OBJECTIVE]       = (string) $this->getObjective();
        $tpl[self::TPL_OBJECTIVE_BONUS] = (string) $this->getObjectiveBonus();
        $tpl[self::TPL_LEAD_TYPE]       = $this->getLeadingType() === self::LEADING_TYPE_EVERY3TURN ? 'end_turn' : 'fight';
        $tpl[self::TPL_LEAD_POWER]      = (string) $this->getLeadingBonus();
        $tpl[self::TPL_COMPLETED]       = $this->isObjectiveCompleted() ? 'completed' : '';
        $tpl[self::TPL_IS_LEADER]       = $this->isLeader();
        $tpl[self::BGA_LOCATION_ARG]       = $this->getLocationArg();

        return $tpl;
    }

    public static function getLineageIds(): array
    {
        return array_map(function(string $code) {
                return self::buildCode($code);
            },[
                Meeple::ELVEN_MAGE,
                Meeple::ELVEN_SAVANT,
                Meeple::ORK_WARRIOR,
                Meeple::ORK_WORKER,
                Meeple::NANI_SAVANT,
                Meeple::NANI_WARRIOR,
                Meeple::HUMANI_MAGE,
                Meeple::HUMANI_WORKER
            ]
        );
    }
}
