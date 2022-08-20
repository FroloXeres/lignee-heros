<?php

namespace LdH\Entity;

use LdH\Entity\Cards\Lineage;

class Meeple
{
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

    public string  $code         = '';
    public string  $name         = '';
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
}
