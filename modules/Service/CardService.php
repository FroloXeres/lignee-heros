<?php

namespace LdH\Service;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\State\MultiChooseLineageState;
use LdH\State\DrawObjectiveState;
use LdH\State\GameInitState;

class CardService
{
    public function updateLdhDeckFromBgaDeck(Deck $ldhDeck)
    {
        $bgaDeck = $ldhDeck->getBgaDeck();
        $cardIds = [];

        foreach ([AbstractCard::LOCATION_DEFAULT, AbstractCard::LOCATION_ON_TABLE, AbstractCard::LOCATION_HAND] as $location) {
            $cards = $bgaDeck->getCardsInLocation($location);
            foreach ($cards as $cardData) {
                $key = sprintf('%s-%s', $cardData['type'], $cardData['type_arg']);

                if (array_key_exists($key, $cardData)) {
                    $cardIds[$key][] = $cardData['id'];
                } else {
                    $cardIds[$key] = [$cardData['id']];
                }
            }
        }

        foreach ($ldhDeck->getCards() as $card) {
            $key = sprintf('%s-%s', $card->getType(), $card->getTypeArg());
            $card->setIds($cardIds[$key] ?? []);
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
     * @param Deck[]  $ldhDeck
     * @param int     $stateId
     * @param int     $currentPlayerId
     *
     * @return array<string, array>
     */
    public function getPublicCards(array $ldhDeck, int $stateId, int $currentPlayerId): array
    {
        $cards = [];

        foreach ($ldhDeck as $type => $deck) {
            if ($this->canSendDeckByStateAndPlayer($type, $stateId, $currentPlayerId)) {
                $cards[$type] = [];

                foreach ($deck->getPublicLocations() as $location) {
                    $cards[$type][$location] = $this->preparePublicData($deck, $location);
                }
            }
        }

        return $cards;
    }

    private function preparePublicData(Deck $ldhDeck, string $location): array
    {
        $bgaCardsData = [];
        $ldhCardsData = $ldhDeck->cardsDataByCode();

        foreach ($ldhDeck->getBgaDeck()->getCardsInLocation($location) as $bgaCardData) {
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
                return $stateId > MultiChooseLineageState::ID;
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
