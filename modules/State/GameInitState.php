<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Repository\MapRepository;
use LdH\Service\CardService;

class GameInitState extends AbstractState
{
    public const ID = 2;
    public const NAME = 'GameInit';

    public static function getId(): int
    {
        return self::ID;
    }

    public function __construct()
    {
        $this->name              = self::NAME;
        $this->type              = self::TYPE_GAME;
        $this->description       = clienttranslate("Game is initializing.");
        $this->action            = 'st' . $this->name;
        $this->args              = 'arg' . $this->name;
        $this->transitions       = ["" => ChooseLineageState::ID];
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
            $city = GameInitState::getRandomCity($game->terrains);
            $game::DbQuery(MapRepository::updateCity($city));

            // -> Draw city inventions (notify)
            /** @var Deck $invention */
            $invention = $game->getDeck(AbstractCard::TYPE_INVENTION);
            $invention->drawCards($game, $city->getInventions());

            // -> Put city units on central tile (notify)


            // Put 8 Worker (- number of player) on central tile (notify)


            // Draw 1st Invention card of the deck (notify)
            $invention->getBgaDeck()->pickCardForLocation(AbstractCard::LOCATION_DEFAULT, AbstractCard::LOCATION_ON_TABLE);

            // Notify players on next state (Notifications don't work on game start)

            $game->gamestate->nextState();
        };
    }

    /**
     * @param array<Terrain|City> $terrains
     *
     * @return City
     */
    protected static function getRandomCity(array $terrains): City
    {
        $cities   = [Terrain::TOWN_HUMANIS, Terrain::TOWN_ORK, Terrain::TOWN_NANI, Terrain::TOWN_ELVEN];
        $cityCode = $cities[array_rand($cities)];

        return $terrains[$cityCode];
    }
}
