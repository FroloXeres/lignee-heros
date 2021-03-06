<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Explore;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Notify\Notify;

class GameInitState extends AbstractState
{
    public const ID = 2;

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = 'GameInit';
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Game is initializing.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = ["" => ChooseLineageState::ID];
    }

    public function getActionMethods(\APP_GameAction $gameAction): ?array
    {
        return [];
    }

    public function getStateArgMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            // No data to send for this
            return [];
        };
    }

    public function getStateActionMethod(\Table $game): ?callable
    {
        return function () use ($game) {
            $notifyList = [];

            // Remove 14 Explore cards
            $exploreDeck     = $game->decks[AbstractCard::TYPE_EXPLORE];
            $exploreDeckSize = $exploreDeck->countCardInLocation(AbstractCard::LOCATION_DEFAULT);
            if ($exploreDeckSize > Explore::DECK_SIZE) {
                $exploreDeck->pickCardsForLocation(
                    $exploreDeckSize - Explore::DECK_SIZE,
                    AbstractCard::LOCATION_DEFAULT,
                    AbstractCard::LOCATION_REMOVED
                );
            }

            // Remove 10 EndTurn cards
            $endTurnDeck     = $game->decks[AbstractCard::TYPE_END_TURN];
            $endTurnDeckSize = $endTurnDeck->countCardInLocation(AbstractCard::LOCATION_DEFAULT);
            if ($endTurnDeckSize > Explore::DECK_SIZE) {
                $endTurnDeck->pickCardsForLocation(
                    $endTurnDeckSize - Explore::DECK_SIZE,
                    AbstractCard::LOCATION_DEFAULT,
                    AbstractCard::LOCATION_REMOVED
                );
            }

            // Choose random city (notify)
            $city = GameInitState::getRandomCity($game);

            // Change middle tile (city)



            $notifyList[] = new Notify(Notify::TYPE_CITY_CHOICE, clienttranslate(sprintf('City will be %s.', $city->getName())), [
                'code' => $city->getCode(),
                'name' => $city->getName()
            ]);

            // -> Draw city inventions (notify)
            // -> Put city units on central tile (notify)


            // Put 8 Worker (- number of player) on central tile (notify)

            // Draw 1st Invention card of the deck (notify)


            // Notify players of game selections
            foreach ($notifyList as $notify) {
                self::notifyAllPlayers($notify->getType(), $notify->getLog(), $notify->getArguments());
            }

            $game->gamestate->nextState("");
        };
    }

    /**
     * @param \Table $game
     *
     * @return Terrain
     */
    protected static function getRandomCity(\Table $game): Terrain
    {
        $cities   = [Terrain::TOWN_HUMANIS, Terrain::TOWN_ORK, Terrain::TOWN_NANI, Terrain::TOWN_ELVEN];
        $cityCode = $cities[array_rand($cities)];

        return $game->terrains[$cityCode];
    }
}
