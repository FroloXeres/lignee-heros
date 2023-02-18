<?php

namespace LdH\Entity\Cards;

use LdH\State\ChooseLineageState;


abstract class AbstractCard implements CardInterface
{
    public const TYPE_DISEASE     = 'disease';
    public const TYPE_FIGHT     = 'fight';
    public const TYPE_OTHER     = 'other';
    public const TYPE_MAGIC     = 'spell';
    public const TYPE_INVENTION = 'invention';
    public const TYPE_OBJECTIVE = 'objective';
    public const TYPE_LINEAGE   = 'lineage';

    /**
     * @var BoardCardInterface[]
     */
    protected array $boardCards = [];

    protected string $code       = '';

    /**
     * @column="card_type"
     * @isKey
     */
    protected string $type;

    /**
     * @column="card_type_arg"
     * @isKey
     */
    protected ?int   $type_arg;

    protected ?int   $nbr;

    protected string  $name        = '';
    protected string  $description = '';
    protected string  $costIcon    = 'none';
    protected string  $artist      = '';

    public static function getBoardCardClassByCard(): string
    {
        return DefaultBoardCard::class;
    }

    /**
     * @return BoardCardInterface[]
     */
    public function getBoardCards(): array
    {
        return $this->boardCards;
    }

    public function getBoardCardsByLocation(string $location, ?int $stateId = null, ?int $playerId = null): array
    {
        return array_filter(
            $this->boardCards,
            function (BoardCardInterface $boardCard) use ($location, $stateId, $playerId) {
                if ($boardCard->getLocation() !== $location) return false;
                if ($stateId === null && $playerId === null) return true;

                switch (get_class($this)) {
                case Lineage::class:
                    if ($location === BoardCardInterface::LOCATION_HAND) return true;
                    return $stateId === ChooseLineageState::ID;
                case Objective::class:
                    if ($location === BoardCardInterface::LOCATION_HAND) return $boardCard->getLocationArg() === $playerId;
                    return false;
                default:
                    return true;
                }
            },
        );
    }

    public function getBoardCard(?int $id = null): ?BoardCardInterface
    {
        $nbBoardCard = $this->getCardCount();
        if ($id === null && $nbBoardCard === 1) {
            return reset($this->boardCards);
        }

        for ($i = 0; $i < $nbBoardCard; $i++) {
            if ($this->boardCards[$i]->getId() === $id) {
                return $this->boardCards[$i];
            }
        }

        return null;
    }

    public function addBoardCard(BoardCardInterface $boardCard): void
    {
        $this->boardCards[] = $boardCard;
    }

    /**
     * @param BoardCardInterface[] $boardCards
     */
    public function setBoardCards(array $boardCards): void
    {
        $this->boardCards = $boardCards;
    }

    public function getCardCount(): int
    {
        return count($this->boardCards);
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
        $this->code = $code;
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
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int|null
     */
    public function getTypeArg(): ?int
    {
        return $this->type_arg;
    }

    /**
     * @param int|null $type_arg
     */
    public function setTypeArg(?int $type_arg): void
    {
        $this->type_arg = $type_arg;
    }

    public function moveCardsTo(string $location, ?int $locationArg = null): void
    {
        foreach ($this->boardCards as $boardCard) {
            $boardCard->setLocation($location);

            if ($locationArg !== null) {
                $boardCard->setLocationArg($locationArg);
            }
        }
    }

    public function moveCardTo(int $id, string $location, ?int $locationArg = null): void
    {
        $boardCard = $this->getBoardCard($id);
        if ($boardCard !== null) {
            $boardCard->setLocation($location);

            if ($locationArg !== null) {
                $boardCard->setLocationArg($locationArg);
            }
        }
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
     * @return AbstractCard
     */
    public function setName(string $name): AbstractCard
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
     * @return AbstractCard
     */
    public function setDescription(string $description): AbstractCard
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getCostIcon(): string
    {
        return $this->costIcon;
    }

    public function setCostIcon(string $costIcon): self
    {
        $this->costIcon = $costIcon;

        return $this;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     *
     * @return AbstractCard
     */
    public function setArtist(string $artist): AbstractCard
    {
        $this->artist = $artist;

        return $this;
    }

    public const TPL_ID        = 'id';
    public const TPL_DECK      = 'deck';
    public const TPL_LARGE     = 'large';
    public const TPL_ICON      = 'icon';
    public const TPL_MEEPLE    = 'meeple';
    public const TPL_COST      = 'cost';
    public const TPL_COST_ICON = 'costIcon';
    public const TPL_NAME      = 'name';
    public const TPL_TYPE_ICON = 'typeIcon';
    public const TPL_TYPE      = 'type';
    public const TPL_NEED_1    = 'need1';
    public const TPL_NEED_2    = 'need2';
    public const TPL_GAIN_TYPE = 'gainType';
    public const TPL_GAIN      = 'gain';
    public const TPL_GAIN_1    = 'gainOrAnd1';
    public const TPL_GAIN_2    = 'gainOrAnd2';
    public const TPL_TEXT_BOLD = 'textBold';
    public const TPL_TEXT      = 'text';
    public const TPL_ARTIST    = 'artist';

    public const TPL_MEEPLE_POWER    = 'meeplePower';
    public const TPL_OBJECTIVE       = 'objective';
    public const TPL_OBJECTIVE_BONUS = 'objectiveBonus';
    public const TPL_LEAD_TYPE       = 'leadType';
    public const TPL_LEAD_POWER      = 'leadPower';
    public const TPL_TYPE_EMPTY      = 'empty';
    public const TPL_COMPLETED       = 'completed';
    public const TPL_IS_LEADER       = 'leader';

    public const BGA_TYPE         = 'type';
    public const BGA_TYPE_ARG     = 'type_arg';
    public const BGA_NBR          = 'nbr';

    public function addPrivateFields(array $tpl, ?int $playerId = null): array
    {
        return $tpl;
    }

    /**
     * Return data for Card module
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::BGA_TYPE         => $this->getType(),
            self::BGA_TYPE_ARG     => $this->getTypeArg(),
            self::BGA_NBR          => 1
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
        return $this->addPrivateFields([
            self::TPL_ID              => $this->getCode(),
            self::TPL_DECK            => $deck->getType(),
            self::TPL_LARGE           => ($deck->isLarge()? 'large' : ''),
            self::TPL_ICON            => 'none',
            self::TPL_COST            => '',
            self::TPL_COST_ICON       => $this->getCostIcon(),
            self::TPL_NAME            => $this->getName(),
            self::TPL_TYPE_ICON       => 'none',
            self::TPL_TYPE            => '',
            self::TPL_NEED_1          => '',
            self::TPL_NEED_2          => '',
            self::TPL_GAIN_TYPE       => self::TPL_TYPE_EMPTY,
            self::TPL_GAIN            => '',
            self::TPL_GAIN_1          => '',
            self::TPL_GAIN_2          => '',
            self::TPL_TEXT_BOLD       => '',
            self::TPL_TEXT            => $this->getDescription(),
            self::TPL_ARTIST          => $this->getArtist(),
            self::TPL_MEEPLE          => '',
            self::TPL_MEEPLE_POWER    => '',
            self::TPL_OBJECTIVE       => '',
            self::TPL_OBJECTIVE_BONUS => '',
            self::TPL_LEAD_POWER      => '',
            self::TPL_LEAD_TYPE       => '',
            self::TPL_COMPLETED       => '',
            self::TPL_IS_LEADER       => '',
        ], $playerId);
    }
}
