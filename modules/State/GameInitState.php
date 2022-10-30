<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Repository\MapRepository;

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

    public function getStateArgMethod(): ?callable
    {
        return function () {
            // No data to send for this
            return [];
        };
    }

    public function getStateActionMethod(): ?callable
    {
        return function () {
            /** @var \ligneeheros $this */
            $mapRepository = new MapRepository();

            // Choose random city (notify)
            $city = GameInitState::getRandomCity($this->terrains);
            $mapRepository->updateCity($city);

            // -> Draw city inventions (notify)
            /** @var Deck $inventions */
            $inventions = $this->getDeck(AbstractCard::TYPE_INVENTION);
            $inventions->drawCards($this, $city->getInventions());

            // -> Put city units on central tile (notify)


            // Put 8 Worker (- number of player) on central tile (notify)


            // Draw 1st Spell card of the deck (notify)
            $spells = $this->getDeck(AbstractCard::TYPE_MAGIC);
            $spells->getBgaDeck()->pickCardForLocation(AbstractCard::LOCATION_DEFAULT, AbstractCard::LOCATION_ON_TABLE);

            // Notify players on next state (Notifications don't work on game start)

            $this->gamestate->nextState();
        };
    }

    /**
     * @param array<Terrain|City> $terrains
     *
     * @return City
     */
    public static function getRandomCity(array $terrains): City
    {
        $cities   = [Terrain::TOWN_HUMANIS, Terrain::TOWN_ORK, Terrain::TOWN_NANI, Terrain::TOWN_ELVEN];
        $cityCode = $cities[array_rand($cities)];

        return $terrains[$cityCode];
    }
}
