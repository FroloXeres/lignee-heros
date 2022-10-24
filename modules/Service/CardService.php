<?php

namespace LdH\Service;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\State\ChooseLineageState;
use LdH\State\DrawObjectiveState;

class CardService
{
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
                $bgaCardsData[] = $ldhCardsData[$codeType];
            } else if (array_key_exists($codeTypeArg, $ldhCardsData)) {
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
                return $stateId === ChooseLineageState::ID;
            case AbstractCard::TYPE_OBJECTIVE:
                // Needed ?
                return $stateId === DrawObjectiveState::ID;
            case AbstractCard::TYPE_OTHER:
            case AbstractCard::TYPE_FIGHT:
            case AbstractCard::TYPE_DISEASE:
                // To update
                return $stateId === 0;
        }

        return false;
    }
}
