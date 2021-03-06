<?php

namespace LdH\Entity\Cards;

class Deck implements \Iterator
{
    public const TYPE_END_TURN  = 'end_turn';
    public const TYPE_EXPLORE   = 'explore';
    public const TYPE_MAGIC     = 'magic';
    public const TYPE_INVENTION = 'invention';
    public const TYPE_OBJECTIVE = 'objective';
    public const TYPE_LINEAGE   = 'lineage';

    protected string $type     = '';
    protected bool   $isLarge  = false;
    protected bool   $canDraw  = false;
    protected bool   $isPublic = false;

    protected string $name = '';

    /** @var AbstractCard[] */
    protected array $cards   = [];
    protected int   $current = 0;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Deck
     */
    public function setType(string $type): Deck
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return AbstractCard[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @param AbstractCard $card
     *
     * @return Deck
     */
    public function addCard(AbstractCard $card): Deck
    {
        $this->cards[] = $card;

        return $this;
    }

    /**
     * @param AbstractCard[] $cards
     */
    public function setCards(array $cards): void
    {
        $this->cards = $cards;
    }

    /**
     * @return bool
     */
    public function isLarge(): bool
    {
        return $this->isLarge;
    }

    /**
     * @param bool $isLarge
     *
     * @return Deck
     */
    public function setIsLarge(bool $isLarge): Deck
    {
        $this->isLarge = $isLarge;

        return $this;
    }

    /**
     * @return bool
     */
    public function canDraw(): bool
    {
        return $this->canDraw;
    }

    /**
     * @param bool $canDraw
     *
     * @return Deck
     */
    public function setCanDraw(bool $canDraw): Deck
    {
        $this->canDraw = $canDraw;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     *
     * @return Deck
     */
    public function setIsPublic(bool $isPublic): Deck
    {
        $this->isPublic = $isPublic;

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
     * @return Deck
     */
    public function setName(string $name): Deck
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_map(
            function($card) {
                return $card->toArray();
            },
            $this->cards
        );
    }

    // Implement Traversable
    public function current()
    {
        return $this->cards[$this->current];
    }

    public function next()
    {
        $this->current++;
    }

    public function key()
    {
        return $this->current;
    }

    public function valid()
    {
        return $this->current < count($this->cards);
    }

    public function rewind()
    {
        $this->current = 0;
    }
}
