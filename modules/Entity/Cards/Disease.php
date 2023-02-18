<?php

namespace LdH\Entity\Cards;

/**
 * @table="explore_disease"
 * @entityLinked="\LdH\Entity\Cards\DefaultBoardCard"
 */
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

    protected int    $level;

    /**
     * @param int $code
     */
    public function __construct(int $level, int $code)
    {
        $this->setLevel($level);
        $this->setCode($code);

        // Card specific
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
     */
    public function setCode(string $code): void
    {
        $this->code = self::TYPE_DISEASE . '_' . $code;
        $this->setTypeArg($code);
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

    /**
     * Return data for Card template build
     *
     * @param Deck $deck
     *
     * @return array
     */
    public function toTpl(Deck $deck, ?int $playerId = null): array
    {
        $tpl = parent::toTpl($deck);

        $tpl[self::TPL_ICON] = AbstractCard::TYPE_DISEASE;
        $tpl[self::TPL_COST] = join('', array_fill(0, $this->getLevel(), 'I'));

        return $tpl;
    }
}
