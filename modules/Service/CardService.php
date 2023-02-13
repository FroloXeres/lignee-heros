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

    public function pickCardForLocation(Deck $deck, string $from, string $to, ?int $toArg = null): ?AbstractCard
    {
        $picked = $deck->getBgaDeck()->pickCardForLocation($from, $to, $toArg ?? 0);
        return $this->updateCardAfterPick($deck, $picked);
    }

    public function moveTheseCardsTo(
        array $cards,
        string $location = BoardCardInterface::LOCATION_HAND,
        int $locationArg = BoardCardInterface::LOCATION_ARG_DEFAULT
    ): void {
        $firstOne = reset($cards);
        if (!$firstOne instanceof AbstractCard) return;

        $repository = $this->getCardRepoByCard($firstOne);
        $repository->updateCardsFromDb($cards);
        $repository->moveCardsTo($cards, $location, $locationArg);
    }

    public function updateCard(AbstractCard $card, array $filters = []): void
    {
        $boardCards = $card->getBoardCards();
        if(empty($boardCards) || !$boardCards[0]->getId()) {
            throw new \BgaUserException('You try to update cards before initializing it');
        }

        $this->getCardRepoByCard($card)->updateAllCards($card, $filters);
    }

    public function updateCardFromDb(AbstractCard $card): void
    {
        $this->getCardRepoByCard($card)->updateCardFromDb($card);
    }

    public function updateCardAfterPick(Deck $deck, array $cardData): ?AbstractCard
    {
        $card = $deck->getFirstCardByKey($cardData['type'], $cardData['type_arg'] ?? null);

        if ($card !== null) {
            $boardCard = $card->getBoardCard();
            $boardCard->setId((int) $cardData['id']);
            $boardCard->setLocation($cardData['location']);
            $boardCard->setLocationArg((int) $cardData['location_arg']);
        }

        return $card;
    }

    public function updateCardsFromDb(Deck $deck): void
    {
        $this->getCardRepoByType($deck->getType())
             ->updateCardsFromDb($deck->getCards())
        ;
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
            if ($this->canSendDeckByState($type, $stateId)) {
                $cards[$type] = [];

                $cardRepository = $this->getCardRepoByType($type);
                $cardRepository->updateCardsFromDb($deck->getCards());

                foreach ($deck->getPublicLocations($stateId) as $location) {
                    $cards[$type][$location] = $this->preparePublicData($deck, $location, $stateId, $currentPlayerId);
                }
            }
        }

        return $cards;
    }

    public function preparePublicData(Deck $ldhDeck, string $location, int $stateId, int $playerId): array
    {
        $bgaCardsData = [];
        $ldhCardsData = $ldhDeck->cardsDataByCode();

        foreach ($ldhDeck->getCards() as $card) {
            $boardCards = $card->getBoardCardsByLocation($location, $stateId, $playerId);
            if (empty($boardCards)) {
                continue;
            }

            $codeType    = $ldhDeck->getType() . '_' . $card->getType();
            $codeTypeArg = $ldhDeck->getType() . '_' . $card->getTypeArg();
            $ldhCode     = array_key_exists($codeTypeArg, $ldhCardsData) ? $codeTypeArg : $codeType;

            foreach ($boardCards as $boardCard) {
                if ($ldhDeck->getType() === AbstractCard::TYPE_LINEAGE) {
                    $ldhCardsData[$codeType][BoardCardInterface::BGA_LOCATION] = $boardCard->getLocation();
                    $ldhCardsData[$ldhCode][BoardCardInterface::BGA_LOCATION_ARG] = $boardCard->getLocationArg();
                }

                $bgaCardsData[] = $ldhCardsData[$ldhCode];
            }
        }

        return $bgaCardsData;
    }

    /**
     * @todo To implement (Depends on states)
     */
    private function canSendDeckByState(string $deckType, int $stateId): bool
    {
        switch ($deckType) {
            case AbstractCard::TYPE_INVENTION:
            case AbstractCard::TYPE_MAGIC:
                // To update
                return $stateId > ChooseLineageState::ID;
            case AbstractCard::TYPE_LINEAGE:
            case AbstractCard::TYPE_OBJECTIVE:
                return true;
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
