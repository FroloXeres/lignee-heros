<?php

namespace LdH\Entity\Cards;

use LdH\State\ChooseLineageState;


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
    public function getCards(bool $orderedByName = false): array
    {
        if ($orderedByName) {
            usort($this->cards, function(AbstractCard $a, AbstractCard $b) {
                return $a->getName() > $b->getName() ? 1 : ($a->getName() < $b->getName() ? -1 : 0);
            });
        }
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

        $boardCardType = $card->getBoardCardClassByCard();
        for ($i = 0; $i < $count; $i++) {
            $boardCard = DefaultBoardCard::isHiddenCard($card) ?
                $boardCardType::buildBoardCard(BoardCardInterface::LOCATION_HIDDEN) :
                $boardCardType::buildBoardCard()
            ;
            $card->addBoardCard($boardCard);
        }

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

    public function cardsDataByCode(?int $playerId = null): array
    {
        return array_combine(
            array_map(function(AbstractCard $card) {return $card->getCode();}, $this->getCards()),
            array_map(
                function(AbstractCard $card) use($playerId) {
                    return $card->toTpl($this, $playerId);
                },
                $this->getCards()
            )
        );
    }

    /**
     * @return array
     */
    public function getBgaDeckData(bool $hidden = false): array
    {
        $cards = [];
        for ($i = 0; $i <= $this->current; $i++) {
            $card        = $this->cards[$i]->toArray();
            $card['nbr'] = $this->cards[$i]->getCardCount();

            $boardCard = $this->cards[$i]->getBoardCard();
            if ($boardCard->getLocation() !== ($hidden? BoardCardInterface::LOCATION_HIDDEN : BoardCardInterface::LOCATION_DEFAULT))
                continue;

            $cards[] = $card;
        }
        return $cards;
    }

    /**
     * Get public location list for this card type
     *
     * @return string[]
     */
    public function getPublicLocations(int $stateId): array
    {
        switch ($this->type) {
            case AbstractCard::TYPE_INVENTION:
            case AbstractCard::TYPE_MAGIC:
                return [
                    BoardCardInterface::LOCATION_DEFAULT,
                    BoardCardInterface::LOCATION_ON_TABLE,
                    BoardCardInterface::LOCATION_HAND
                ];
            case AbstractCard::TYPE_LINEAGE:
                $locations = [];
                if ($stateId === ChooseLineageState::ID) {
                    $locations[] = BoardCardInterface::LOCATION_DEFAULT;
                }
                if ($stateId >= ChooseLineageState::ID)  {
                    $locations[] = BoardCardInterface::LOCATION_HAND;
                }
                return $locations;
            case AbstractCard::TYPE_OBJECTIVE:
                return ($stateId >= ChooseLineageState::ID) ? [BoardCardInterface::LOCATION_HAND] : [];
            case AbstractCard::TYPE_DISEASE:
            case AbstractCard::TYPE_FIGHT:
            case AbstractCard::TYPE_OTHER:
                return [
                    BoardCardInterface::LOCATION_DEFAULT
                ];
            default:
                return [];
        }
    }

    public function getFirstCardByCode(string $code): ?AbstractCard
    {
        foreach ($this->cards as $card) {
            if ($card->getCode() === $code) {
                return $card;
            }
        }

        return null;
    }

    public function getFirstCardByKey(string $type, ?int $typeArg = null): ?AbstractCard
    {
        foreach ($this->cards as $card) {
            if ($card->getType() === $type && (!$typeArg || $card->getTypeArg() === $typeArg)) {
                return $card;
            }
        }

        return null;
    }

    /** @return array<AbstractCard> */
    public function getCardsOnLocation(string $location): array
    {
        return array_filter(
            $this->cards,
            function (AbstractCard $card) use ($location) {
                return count(
                    $card->getBoardCardsByLocation(
                        $location
                    )
                );
            }
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
        return $this->current && $this->current < count($this->cards);
    }

    public function rewind()
    {
        $this->current = 0;
    }
}
