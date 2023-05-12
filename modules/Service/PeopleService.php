<?php

namespace LdH\Service;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Cards\BoardCardInterface;
use LdH\Entity\Map\Terrain;
use LdH\Object\Coordinate;
use LdH\Object\MapUnit;
use LdH\Object\SimpleTile;
use LdH\Object\TileHarvestResources;
use LdH\Repository\CardRepository;
use LdH\Entity\Unit;
use LdH\Entity\Meeple;

class PeopleService implements \JsonSerializable
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

    protected CardService $cardService;
    protected ?CardRepository $unitRepository = null;

    public function __construct()
    {
        $this->cardService = new CardService();
    }

    public function getRepository(): CardRepository
    {
        if ($this->unitRepository === null) {
            $this->unitRepository = $this->cardService->getCardRepository(Unit::class);
        }

        return $this->unitRepository;
    }

    public function getPopulation(): int
    {
        return $this->population;
    }

    public function getUnitById(int $unitId): ?Unit
    {
        $pos = $this->byIds[$unitId] ?? null;
        return $this->units[$pos] ?? null;
    }

    /** @return array<string, array<int, Unit>> */
    public function getByTypeUnits(): array
    {
        $units = [];
        foreach ($this->byType as $type => $unitPosList) {
            $units[$type] = [];
            foreach ($unitPosList as $pos) {
                $units[$type][] = $this->units[$pos];
            }
        }
        return $units;
    }

    public function getUnits(): array
    {
        return $this->units;
    }

    public function getPopulationAsString(): string
    {
        $people = [];
        foreach ($this->byType as $type => $ids) {
            $count = count($ids);
            if ($count) {
                $people[] = sprintf(
                    '%s [%s]',
                    $count,
                    $this->meeples[$type]->getCode(),
                );
            }
        }

        return MessageHelper::formatList($people);
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

    /**
     * @param array<SimpleTile> $simpleMap
     *
     * @return array<int, array<int>>
     */
    public function getUnitPossibleMoves(array $simpleMap, int $maxMove, ?int $unitId = null): array
    {
        $freeUnitPositions = $this->getRepository()->getFreeUnitPositions($unitId);

        $moves = [];
        $paths = [];
        foreach ($freeUnitPositions as $freeUnit) {
            if (!array_key_exists($freeUnit->tileId, $paths)) {
                $paths[$freeUnit->tileId] = [];
                $this->pathfinder($paths[$freeUnit->tileId], $simpleMap, $freeUnit->position, $maxMove);

                if (array_key_exists($freeUnit->position->key(), $paths[$freeUnit->tileId]))
                    unset($paths[$freeUnit->tileId][$freeUnit->position->key()]);
            }

            $moves[$freeUnit->unitId] = $paths[$freeUnit->tileId];
        }

        return array_map(function(array $positions) use($simpleMap) {
            return array_map(
                function(string $key) use($simpleMap) {
                    return $simpleMap[$key]->tileId;
                },
                array_keys($positions)
            );
        }, $moves);
    }



    /**
     * @param array<SimpleTile> $simpleMap
     *
     * @return array<Coordinate>
     */
    protected function pathfinder(array &$paths, array $simpleMap, Coordinate $position, int $moveLeft): void
    {
        foreach ($this->getAround($position) as $target) {
            $alreadyThere = array_key_exists($target->key(), $paths);
            !$alreadyThere && $paths[$target->key()] = $target;
            if (!$alreadyThere && $moveLeft && $simpleMap[$target->key()]->revealed) {
                $this->pathfinder($paths, $simpleMap, $target, $moveLeft - 1);
            }
        }
    }

    /** @return array<Coordinate> */
    protected function getAround(Coordinate $position): array
    {
        $around = [];

        $toX = new Coordinate($position->x + 1, $position->y);
        if (!MapService::isTooFar($toX)) $around[] = $toX;

        $farX = new Coordinate($position->x - 1, $position->y);
        if (!MapService::isTooFar($farX)) $around[] = $farX;

        $toY = new Coordinate($position->x, $position->y + 1);
        if (!MapService::isTooFar($toY)) $around[] = $toY;

        $farY = new Coordinate($position->x, $position->y - 1);
        if (!MapService::isTooFar($farY)) $around[] = $farY;

        $toXfarY = new Coordinate($position->x + 1, $position->y - 1);
        if (!MapService::isTooFar($toXfarY)) $around[] = $toXfarY;

        $farXtoY = new Coordinate($position->x - 1, $position->y + 1);
        if (!MapService::isTooFar($farXtoY)) $around[] = $farXtoY;

        return $around;
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
        $this->byType[CurrentStateService::getStateByMeepleType($unit->getType())][] = $this->population;

        $this->population++;

        return $this;
    }

    /** @return array<Unit> */
    public function freeUnits(): array
    {
        $this->getRepository()->setAllUnitsToStatus(Unit::STATUS_FREE);
        foreach ($this->units as $unit) {
            $unit->setStatus(Unit::STATUS_FREE);
        }

        return $this->units;
    }

    /**
     * @param array<Terrain> $terrains
     *
     * @return array<TileHarvestResources>
     */
    public function getHarvestableResources(array $terrains): array
    {
        $list = $this->getRepository()->getHarvestableResourcesAndUnits();

        // Replace resources array by resource key
        foreach ($list as $tileHarvestResource) {
            $resources = $terrains[$tileHarvestResource->terrain]->getResources();
            $byResourceKey = [];
            foreach ($tileHarvestResource->resources as $key => $used) {
                if ($used === null) break;

                $resourceCode = $resources[$key]->getCode();
                $byResourceKey[$resourceCode] = $used;
            }
            $tileHarvestResource->resources = $byResourceKey;
        }

        return $list;
    }

    /** @return array<Unit> */
    public function isLineageUnitFree(string $type): bool
    {
        // todo: Missing something here?
        return array_key_exists($type, $this->byType);
    }

    public function kill(Unit $unit): void
    {
        foreach ($this->units as $pos => $test) {
            if ($test->getId() === $unit->getId()) {
                $locationPos = array_search($pos, $this->byPlace[$unit->getLocation()] ?? []);
                if($locationPos !== false) unset($this->byPlace[$unit->getLocation()]);

                $typePos = array_search($pos, $this->byType[$unit->getType()->getCode()] ?? []);
                if($typePos !== false) unset($this->byType[$unit->getType()->getCode()]);

                unset($this->byIds[$unit->getId()]);

                $this->getRepository()->killUnit($unit);
                break;
            }
        }
    }

    /** @return array<Unit> */
    public function birth(Meeple $type, string $location = Unit::LOCATION_MAP, int $locationArg = BoardCardInterface::LOCATION_ARG_DEFAULT, int $count = 1, bool $acted = true): array
    {
        $created = [];

        for ($i = 0; $i < $count; $i++) {
            $baby = (new Unit())
                ->setType($type)
                ->setLocation($location)
                ->setLocationArg($locationArg)
                ->setStatus($acted ? Unit::STATUS_ACTED : Unit::STATUS_FREE)
                ->setDisease(null)
            ;
            if ($this->getBgaDeck() !== null) {
                $this->getBgaDeck()->createCards([$baby->toArray()], $location, $locationArg);

                $baby->setId($this->getRepository()->getLastId());

                // No update needed if Free (Default)
                if ($acted) {
                    $this->getRepository()->update($baby);
                }

                $this->addUnit($baby);
            }
            $created[] = $baby;
        }

        return $created;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, Meeple> $meeples
     * @return Unit
     */
    public static function buildUnit(array $data, array $meeples): Unit
    {
        return (new Unit())
            ->setId((int) $data['card_id'])
            ->setType($meeples[$data['card_type']])
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

        foreach ($this->cardService->getCardRepository(Unit::class)->getPeopleData() as $unitData) {
            $this->addUnit(
                self::buildUnit($unitData, $meeples)
            );
        }
    }
    public function jsonSerialize(): array
    {
        return [
            'byType' => $this->byType,
            'byPlace' => $this->byPlace,
            'byIds' => $this->byIds,
            'units' => $this->units
        ];
    }
}
