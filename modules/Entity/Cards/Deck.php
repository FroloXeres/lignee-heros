<?php

namespace LdH\Entity\Cards;

class Deck implements \Iterator
{
    public const TYPE_EXPLORE_DISEASE = 'explore_disease';
    public const TYPE_EXPLORE_FIGHT   = 'explore_fight';
    public const TYPE_EXPLORE_OTHER   = 'explore_other';
    public const TYPE_MAGIC           = 'spell';
    public const TYPE_INVENTION       = 'invention';
    public const TYPE_OBJECTIVE       = 'objective';
    public const TYPE_LINEAGE         = 'lineage';

    protected string $type     = '';
    protected bool   $isLarge  = false;
    protected bool   $canDraw  = false;
    protected bool   $isPublic = false;

    protected string $name = '';

    /** @var AbstractCard[] */
    protected array $cards   = [];
    protected ?int  $current = null;

    /** @var int[] */
    protected array $copies = [];

    /** @var \Deck */
    protected $bgaDeck = null;

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
     * @return \Deck|null
     */
    public function getBgaDeck()
    {
        return $this->bgaDeck;
    }

    /**
     * @param \Deck|null $bgaDeck
     */
    public function setBgaDeck($bgaDeck): void
    {
        $this->bgaDeck = $bgaDeck;
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
     * @param int          $count
     *
     * @return Deck
     */
    public function addCard(AbstractCard $card, int $count = 1): Deck
    {
        if ($this->current === null) {$this->current = 0;}
        else $this->current++;

        $this->cards[$this->current]  = $card;
        $this->copies[$this->current] = $count;

        return $this;
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

    public function getPublicData(): array
    {
        return [
            'name'    => $this->getName(),
            'large'   => $this->isLarge(),
            'canDraw' => $this->canDraw()
        ];
    }

    public function cardsDataByCode(): array
    {
        return array_combine(
            array_map(function(AbstractCard $card) {return $card->getCode();}, $this->getCards()),
            array_map(function(AbstractCard $card) {return $card->toTpl($this);}, $this->getCards())
        );
    }

    /**
     * @return array
     */
    public function getBgaDeckData(): array
    {
        $cards = [];
        for ($i = 0; $i <= $this->current; $i++) {
            $card        = $this->cards[$i]->toArray();
            $card['nbr'] = $this->copies[$i];
            $cards[]     = $card;
        }
        return $cards;
    }

    /**
     * Get public location list for this card type
     *
     * @return string[]
     */
    public function getPublicLocations(): array
    {
        switch ($this->type) {
            case AbstractCard::TYPE_INVENTION:
            case AbstractCard::TYPE_MAGIC:
                return [
                    AbstractCard::LOCATION_DEFAULT,
                    AbstractCard::LOCATION_ON_TABLE,
                    AbstractCard::LOCATION_HAND
                ];
            case AbstractCard::TYPE_LINEAGE:
                return [
                    AbstractCard::LOCATION_DEFAULT,
                    AbstractCard::LOCATION_HAND
                ];
            case AbstractCard::TYPE_OBJECTIVE:
            case AbstractCard::TYPE_DISEASE:
            case AbstractCard::TYPE_FIGHT:
            case AbstractCard::TYPE_OTHER:
                return [
                    AbstractCard::LOCATION_DEFAULT
                ];
            default:
                return [];
        }
    }

    public function getCardByCode(string $code): ?AbstractCard
    {
        foreach ($this->cards as $card) {
            if ($card->getCode() === $code) {
                return $card;
            }
        }

        return null;
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
        return $this->current && $this->current < count($this->cards);
    }

    public function rewind()
    {
        $this->current = 0;
    }
}
