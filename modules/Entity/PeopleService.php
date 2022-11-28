<?php

namespace LdH\Entity;

use LdH\Repository\CardRepository;

class PeopleService
{
    public const CITY_ID = 25;

    protected int $population = 0;

    /** @var Meeple[] */
    protected array $meeples = [];

    /** @var \Deck */
    protected $bgaDeck = null;

    /** @var array<int, Unit> */
    protected array $units = [];

    /** @var array<int, int>  */
    protected array $byIds = [];

    /** @var array<string, int[]>  */
    protected array $byPlace = [
        Unit::LOCATION_MAP       => [],
        Unit::LOCATION_SPELL     => [],
        Unit::LOCATION_INVENTION => []
    ];

    /**
     * @var array<string, int[]>
     */
    protected array $byType = [
        Meeple::WORKER  => [],
        Meeple::WARRIOR => [],
        Meeple::SAVANT  => [],
        Meeple::MAGE    => [],
        Meeple::MONSTER => []
    ];

    protected CardRepository $cardRepository;

    public function __construct()
    {
        $this->cardRepository = new CardRepository();
    }

    public function getPopulation(): int
    {
        return $this->population;
    }

    public function getPopulationAsString(): string
    {
        $people = [];
        foreach ($this->byType as $type => $ids) {
            if (count($ids)) {
                $people[] = sprintf('%s %s', count($ids), $this->meeples[$type]->getName());
            }
        }

        return join(', ', $people);
    }

    /**
     * @return \Deck|null
     */
    public function getBgaDeck()
    {
        return $this->bgaDeck;
    }

    public function setBgaDeck($bgaDeck): self
    {
        $this->bgaDeck = $bgaDeck;

        return $this;
    }

    public function setMeeples(array $meeples): self
    {
        $this->meeples = $meeples;

        return $this;
    }

    public function addUnit(Unit $unit): self
    {
        $this->units[$this->population] = $unit;
        $this->byIds[$unit->getId()] = $this->population;
        $this->byPlace[$unit->getLocation()][] = $this->population;
        $this->byType[$unit->getType()][] = $this->population;

        $this->population++;

        return $this;
    }

    public function birth(string $type, string $location = Unit::LOCATION_MAP, int $locationArg = null, int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            $baby = (new Unit())
                ->setType($type)
                ->setLocation($location)
                ->setLocationArg($locationArg)
                ->setStatus(Unit::STATUS_ACTED)
                ->setDisease(null)
            ;
            $this->getBgaDeck()->createCards([$baby->toArray()], $location, $locationArg);
            $baby->setId($this->cardRepository->getLastId());

            $this->addUnit($baby);
        }

    }

    public static function buildUnit(array $data): Unit
    {
        return (new Unit())
            ->setId((int) $data['card_id'])
            ->setType($data['card_type'])
            ->setLocation($data['card_location'])
            ->setLocationArg($data['card_location_arg'])
            ->setStatus($data['meeple_status'])
            ->setDisease($data['meeple_sick'])
            ;
    }

    /**
     * @param \Deck    $bgaMeeple
     * @param Meeple[] $meeples
     */
    public function init(\Deck $bgaMeeple, array $meeples): void
    {
        $this->setBgaDeck($bgaMeeple);
        $this->setMeeples($meeples);

        foreach ($this->cardRepository->getPeopleData() as $unitData) {
            $this->addUnit(
                self::buildUnit($unitData)
            );
        }
    }
}
