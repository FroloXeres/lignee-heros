<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Repository\MapRepository;

class GameInitState extends AbstractState
{
    public    const ID = 2;
    protected const METHOD_INIT_GAME = 'gameInit';
    public static function getId(): int {return self::ID;}

    public function __construct()
    {
        $this->name              = 'GameInit';
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Game is initializing.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = ["" => MultiChooseLineageState::ID];
    }

    public function getActionMethods(\APP_GameAction $gameAction): ?array
    {
        return [
            self::METHOD_INIT_GAME => [$this, self::METHOD_INIT_GAME],
        ];
    }

    public function gameInit(\APP_GameAction $gameAction)
    {
        die('In gameInit action');
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
            // Choose random city (notify)
            $city = GameInitState::getRandomCity($game);

            // Change middle tile (city)
            $sql = MapRepository::updateCity($city);
            $game::DbQuery($sql);

            // -> Draw city inventions (notify)
            /** @var Deck $inventionDeck */
            $inventionDeck = $game->cards[AbstractCard::TYPE_INVENTION];
            /*
            $inventionDeck->getBgaDeck()->moveCards(
                $inventionDeck->getFirstCardIdsByTypeArg(array_map(
                    function(Invention $invention) {
                        return $invention->getTypeArg();
                    },
                    $city->getInventions()
                )),
                AbstractCard::LOCATION_HAND
            );
            */

            // -> Put city units on central tile (notify)


            // Put 8 Worker (- number of player) on central tile (notify)


            // Draw 1st Invention card of the deck (notify)


            // Notify players on next state (Notifications don't work on game start)

            $game->gamestate->nextState();
        };
    }

    /**
     * @param \Table $game
     *
     * @return Terrain
     */
    protected static function getRandomCity(\Table $game): City
    {
        $cities   = [Terrain::TOWN_HUMANIS, Terrain::TOWN_ORK, Terrain::TOWN_NANI, Terrain::TOWN_ELVEN];
        $cityCode = $cities[array_rand($cities)];

        return $game->terrains[$cityCode];
    }
}
