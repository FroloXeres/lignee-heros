<?php

namespace LdH\Service;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\State\ChooseLineageState;
use LdH\State\DrawObjectiveState;
use LdH\State\GameInitState;

class CardService
{
    public function getPublicDecks(array $bgaDecks): array
    {
        return array_combine(
            array_keys($bgaDecks),
            array_map(function(Deck $bgaDeck) {
                return $bgaDeck->getPublicData();
            }, $bgaDecks)
        );
    }

    /**
     * @param \Deck[] $bgaDeck
     * @param Deck[]  $ldhDeck
     * @param int     $stateId
     * @param int     $currentPlayerId
     *
     * @return array<string, array>
     */
    public function getPublicCards(array $bgaDeck, array $ldhDeck, int $stateId, int $currentPlayerId): array
    {
        $cards = [];

        foreach ($ldhDeck as $type => $deck) {
            if ($this->canSendDeckByStateAndPlayer($type, $stateId, $currentPlayerId)) {
                $cards[$type] = [];

                foreach ($deck->getPublicLocations() as $location) {
                    $cards[$type][$location] = $this->preparePublicData($bgaDeck[$type], $deck, $location);
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
                // $stateId === ChooseLineageState::ID
                return $stateId === GameInitState::ID;
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
