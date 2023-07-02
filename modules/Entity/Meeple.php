<?php

namespace LdH\Entity;

use LdH\Entity\Cards\Lineage;

class Meeple
{
    public const BGA_TYPE = 'meeple';

    public const MAGE          = 'mage';
    public const WORKER        = 'worker';
    public const WARRIOR       = 'warrior';
    public const SAVANT        = 'savant';
    public const ALL           = 'all';
    public const MONSTER       = 'monster';
    public const ELVEN_MAGE    = 'elven_mage';
    public const ELVEN_SAVANT  = 'elven_savant';
    public const NANI_WARRIOR  = 'nani_warrior';
    public const NANI_SAVANT   = 'nani_savant';
    public const HUMANI_MAGE   = 'humani_mage';
    public const HUMANI_WORKER = 'humani_worker';
    public const ORK_WARRIOR   = 'ork_warrior';
    public const ORK_WORKER    = 'ork_worker';

    public const HARVESTERS = [
        self::WORKER,
        self::ORK_WORKER,
        self::HUMANI_WORKER
    ];
    public const WARRIORS = [
        self::WARRIOR,
        self::ORK_WARRIOR,
        self::NANI_WARRIOR
    ];

    public const MAGES = [
        self::MAGE,
        self::HUMANI_MAGE,
        self::ELVEN_MAGE
    ];
    public const SAVANTS = [
        self::SAVANT,
        self::NANI_SAVANT,
        self::ELVEN_SAVANT
    ];

    public string  $code         = '';
    public string  $name         = '';
    public string  $plural       = '';
    public string  $description  = '';
    public ?Lineage $lineage     = null;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
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
     * @return Meeple
     */
    public function setCode(string $code): Meeple
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Meeple
     */
    public function setName(string $name): Meeple
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlural(): string
    {
        return $this->plural;
    }

    /**
     * @param string $plural
     *
     * @return Meeple
     */
    public function setPlural(string $plural): Meeple
    {
        $this->plural = $plural;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Meeple
     */
    public function setDescription(string $description): Meeple
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Lineage|null
     */
    public function getLineage(): ?Lineage
    {
        return $this->lineage;
    }

    /**
     * @param Lineage|null $lineage
     *
     * @return Meeple
     */
    public function setLineage(?Lineage $lineage): Meeple
    {
        $this->lineage = $lineage;

        if (!$lineage->getMeeple()) {
            $lineage->setMeeple($this);
        }

        return $this;
    }

    public function getColor(): string
    {
        switch ($this->code) {
            case self::ELVEN_MAGE :
            case self::HUMANI_MAGE :
                return self::MAGE;
            case self::NANI_WARRIOR :
            case self::ORK_WARRIOR :
                return self::WARRIOR;
            case self::HUMANI_WORKER :
            case self::ORK_WORKER :
                return self::WORKER;
            case self::NANI_SAVANT :
            case self::ELVEN_SAVANT :
                return self::SAVANT;
            default: return $this->code;
        }
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'color' => $this->getColor(),
            'description' => $this->description,
            'lineage' => $this->getLineage() ? $this->getLineage()->getCode() : ''
        ];
    }
}
