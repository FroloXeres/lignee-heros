<?php

namespace LdH\Service;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Cards\Disease;
use LdH\Entity\Cards\Fight;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Cards\Other;
use LdH\Entity\Cards\Spell;
use LdH\Repository\CardRepository;
use LdH\State\ChooseLineageState;
use LdH\State\DeadEndState;
use LdH\State\DrawObjectiveState;

class CardService
{
    /**
     * @var array<string, CardRepository>
     */
    protected array $cardRepositories = [];

    public function getCardRepoByCard(AbstractCard $card): ?CardRepository
    {
        return $this->getCardRepository(get_class($card));
    }

    public function getCardRepoByType(string $cardType): ?CardRepository
    {
        return $this->getCardRepository(self::getCardClassByCardType($cardType));
    }

    public function getCardRepository(string $class): ?CardRepository
    {
        $cardType = self::getDeckTypeByCardClass($class);
        if (!isset($this->cardRepositories[$cardType])) {
            $this->cardRepositories[$cardType] = new CardRepository($class);
        }

        return $this->cardRepositories[$cardType] ?? null;
    }

    /**
     * @param Deck           $deck
     * @param AbstractCard[] $cards
     */
    public function drawCards(Deck $deck, array $cards): void
    {
        $cardIds = $this->getCardRepoByType($deck->getType())->getCardIds(
            $deck->getType(),
            array_map(function(AbstractCard $card) {return $card->getTypeArg();}, $cards),
            BoardCardInterface::LOCATION_DEFAULT,
            true
        );

        $deck->getBgaDeck()->moveCards(
            array_keys($cardIds),
            BoardCardInterface::LOCATION_HAND
        );
    }

    public function updateCard(AbstractCard $card, array $filters = []): void
    {
        $this->getCardRepoByCard($card)->update($card, $filters);
    }

    public static function getTypeByCard(AbstractCard $card): string
    {
        return self::getDeckTypeByCardClass(get_class($card));
    }

    public static function getCardClassByCardType(string $cardType): string
    {
        switch ($cardType) {
            case AbstractCard::TYPE_LINEAGE: return Lineage::class;
            case AbstractCard::TYPE_OBJECTIVE: return Objective::class;
            case AbstractCard::TYPE_INVENTION: return Invention::class;
            case AbstractCard::TYPE_MAGIC: return Spell::class;
            case AbstractCard::TYPE_FIGHT: return Fight::class;
            case AbstractCard::TYPE_OTHER: return Other::class;
            case AbstractCard::TYPE_DISEASE: return Disease::class;
            default: return '';
        }
    }

    public static function getDeckTypeByCardClass(string $class): string
    {
        switch ($class) {
            case Lineage::class: return Deck::TYPE_LINEAGE;
            case Objective::class: return Deck::TYPE_OBJECTIVE;
            case Invention::class: return Deck::TYPE_INVENTION;
            case Spell::class: return Deck::TYPE_MAGIC;
            case Fight::class: return Deck::TYPE_EXPLORE_FIGHT;
            case Other::class: return Deck::TYPE_EXPLORE_OTHER;
            case Disease::class: return Deck::TYPE_EXPLORE_DISEASE;
            default: return '';
        }
    }

    protected function populateDeckWithIds(Deck $deck)
    {
        $cardIds = $this->getCardRepoByType($deck->getType())->getCardIds(
            $deck->getType(),
            array_map(function(AbstractCard $card) {return $card->getTypeArg();}, $deck->getCards())
        );

        $cardsIdsByTypeArg = [];
        foreach ($cardIds as $cardId => $typeArg) {
            if (array_key_exists($typeArg, $cardsIdsByTypeArg)) {
                $cardsIdsByTypeArg[$typeArg][] = $cardId;
            } else {
                $cardsIdsByTypeArg[$typeArg] = [$cardId];
            }
        }

        foreach ($deck->getCards() as $card) {
            $card->setBoardCards(
                $cardsIdsByTypeArg[$card->getTypeArg()] ?? []
            );
        }
    }

    public function getPublicDecks(array $decks): array
    {
        return array_combine(
            array_keys($decks),
            array_map(function(Deck $deck) {
                return $deck->getPublicData();
            }, $decks)
        );
    }

    /**
     * @param Deck[]  $decks
     * @param int     $stateId
     * @param int     $currentPlayerId
     *
     * @return array<string, array>
     */
    public function getPublicCards(array $decks, int $stateId, int $currentPlayerId): array
    {
        $cards = [];

        foreach ($decks as $type => $deck) {
            if ($this->canSendDeckByStateAndPlayer($type, $stateId, $currentPlayerId)) {
                $cards[$type] = [];

                foreach ($deck->getPublicLocations() as $location) {
                    $cards[$type][$location] = $this->preparePublicData($deck->getBgaDeck(), $deck, $location);
                }
            }
        }

        return $cards;
    }

    private function preparePublicData(\Deck $bgaDeck, Deck $ldhDeck, string $location): array
    {
        $bgaCardsData = [];
        $ldhCardsData = $ldhDeck->cardsDataByCode();

        foreach ($bgaDeck->getCardsInLocation($location) as $bgaCardData) {
            $codeType    = $ldhDeck->getType() . '_' . $bgaCardData['type'];
            $codeTypeArg = $ldhDeck->getType() . '_' . $bgaCardData['type_arg'];

            if (array_key_exists($codeType, $ldhCardsData)) {
                if ($ldhDeck->getType() === AbstractCard::TYPE_LINEAGE) {
                    $ldhCardsData[$codeType][BoardCardInterface::BGA_LOCATION] = $bgaCardData['location'];
                    $ldhCardsData[$codeType][BoardCardInterface::BGA_LOCATION_ARG] = $bgaCardData['location_arg'];
                }

                $bgaCardsData[] = $ldhCardsData[$codeType];
            } else if (array_key_exists($codeTypeArg, $ldhCardsData)) {
                if ($ldhDeck->getType() === AbstractCard::TYPE_LINEAGE) {
                    $ldhCardsData[$codeType][BoardCardInterface::BGA_LOCATION] = $bgaCardData['location'];
                    $ldhCardsData[$codeTypeArg][BoardCardInterface::BGA_LOCATION_ARG] = $bgaCardData['location_arg'];
                }

                $bgaCardsData[] = $ldhCardsData[$codeTypeArg];
            }
        }

        return $bgaCardsData;
    }

    /**
     * @todo To implement (Depends on states)
     */
    private function canSendDeckByStateAndPlayer(string $deckType, int $stateId, int $currentPlayerId): bool
    {
        switch ($deckType) {
            case AbstractCard::TYPE_INVENTION:
            case AbstractCard::TYPE_MAGIC:
                // To update
                return $stateId > ChooseLineageState::ID;
            case AbstractCard::TYPE_LINEAGE:
                return true;
            case AbstractCard::TYPE_OBJECTIVE:
                // Needed ?
                return \in_array($stateId, [DrawObjectiveState::ID], true);
            case AbstractCard::TYPE_OTHER:
            case AbstractCard::TYPE_FIGHT:
            case AbstractCard::TYPE_DISEASE:
                // To update
                return $stateId === DeadEndState::ID;
        }

        return false;
    }
}
