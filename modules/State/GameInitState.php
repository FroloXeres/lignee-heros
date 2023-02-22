<?php

namespace LdH\State;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Cards\Deck;
use LdH\Entity\Map\City;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Meeple;
use LdH\Entity\Unit;
use LdH\Service\PeopleService;
use LdH\Service\CurrentStateService;

class GameInitState extends AbstractState
{
    public const ID = 2;
    public const NAME = 'GameInit';

    public const NOTIFY_CITY_START         = 'cityStart';
    public const NOTIFY_CITY_INVENTIONS    = 'cityInventions';

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

            // Choose random city (notify)
            $city = GameInitState::getRandomCity($this->terrains);
            $this->mapService->updateCity($city);

            // -> Draw city inventions (notify)
            /** @var Deck $inventions */
            $inventions = $this->getDeck(AbstractCard::TYPE_INVENTION);
            $this->getCardService()->moveTheseCardsTo($city->getInventions());

            // Draw 1st invention card of the deck (notify)
            $inventions->getBgaDeck()->pickCardForLocation(BoardCardInterface::LOCATION_DEFAULT, BoardCardInterface::LOCATION_ON_TABLE);

            $peopleService = $this->getPeople();

            // -> Put city units on central tile (notify)
            foreach ($city->getUnits() as $meeple) {
                $peopleService->birth(
                    $meeple,
                    Unit::LOCATION_MAP,
                    PeopleService::CITY_ID,
                    1,
                    false
                );

                $this->incGameStateValue(CurrentStateService::getStateByMeepleType($meeple), 1);
            }

            // Put 10 Worker (- number of player - city units) on central tile (notify)
            $toAddCnt = CurrentStateService::START_PEOPLE - $this->getPlayersNumber() - count($city->getUnits());
            $peopleService->birth(
                $this->meeples[Meeple::WORKER],
                Unit::LOCATION_MAP,
                PeopleService::CITY_ID,
                $toAddCnt,
                false
            );
            $this->incGameStateValue(CurrentStateService::GLB_WORKER_CNT, $toAddCnt);

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
