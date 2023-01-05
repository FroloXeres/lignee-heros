<?php

namespace LdH\Service;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\Repository\CardRepository;
use LdH\State\ChooseLineageState;
use LdH\State\DeadEndState;
use LdH\State\DrawObjectiveState;

class CardService
{
    protected CardRepository $cardRepository;

    public function __construct()
    {
        $this->cardRepository = new CardRepository();
    }

    /**
     * @param Deck           $deck
     * @param AbstractCard[] $cards
     */
    public function drawCards(Deck $deck, array $cards): void
    {
        $cardIds = $this->cardRepository->getCardIds(
            $deck->getType(),
            array_map(function(AbstractCard $card) {return $card->getTypeArg();}, $cards),
            AbstractCard::LOCATION_DEFAULT,
            true
        );

        $deck->getBgaDeck()->moveCards(
            array_keys($cardIds),
            AbstractCard::LOCATION_HAND
        );
    }

    protected function populateDeckWithIds(Deck $deck)
    {
        $cardIds = $this->cardRepository->getCardIds(
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
            $card->setIds(
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
                    $ldhCardsData[$codeType][AbstractCard::BGA_LOCATION] = $bgaCardData['location'];
                    $ldhCardsData[$codeType][AbstractCard::BGA_LOCATION_ARG] = $bgaCardData['location_arg'];
                }

                $bgaCardsData[] = $ldhCardsData[$codeType];
            } else if (array_key_exists($codeTypeArg, $ldhCardsData)) {
                if ($ldhDeck->getType() === AbstractCard::TYPE_LINEAGE) {
                    $ldhCardsData[$codeType][AbstractCard::BGA_LOCATION] = $bgaCardData['location'];
                    $ldhCardsData[$codeTypeArg][AbstractCard::BGA_LOCATION_ARG] = $bgaCardData['location_arg'];
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
