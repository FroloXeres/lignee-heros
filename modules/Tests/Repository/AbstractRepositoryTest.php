<?php

namespace LdH\Tests\Repository;

use LdH\Entity\Cards\Disease;
use LdH\Entity\Cards\Fight;
use LdH\Entity\Cards\Invention;
use LdH\Entity\Cards\Lineage;
use LdH\Entity\Cards\Objective;
use LdH\Entity\Cards\Other;
use LdH\Entity\Cards\Spell;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\Tile;
use LdH\Entity\Meeple;
use LdH\Repository\AbstractRepository;
use LdH\Repository\Field;
use PHPUnit\Framework\TestCase;

require_once "./../Dummy/APP_DbObject.php";

class AbstractRepositoryTest extends TestCase
{
    protected AbstractRepository $repository;

    public function testMapRepositoryMetadata()
    {
        $reflexion = $this->initRepository(Tile::class);

        $classProp = $reflexion->getProperty('class');
        $classProp->setAccessible(true);
        $this->assertEquals(Tile::class, $classProp->getValue($this->repository));

        $tableProp = $reflexion->getProperty('table');
        $tableProp->setAccessible(true);
        $this->assertEquals('map', $tableProp->getValue($this->repository));

        $keysProp = $reflexion->getProperty('keys');
        $keysProp->setAccessible(true);
        $this->assertEquals(['id'], $keysProp->getValue($this->repository));

        $mappedProp = $reflexion->getProperty('mappedFields');
        $mappedProp->setAccessible(true);
        $mapped = $mappedProp->getValue($this->repository);
        $this->assertEquals(['id', 'x', 'y', 'howFar', 'flip', 'disabled', 'terrain'], array_keys($mapped));

        foreach ($mapped as $field) {
            $this->assertInstanceOf(Field::class, $field);
        }

        $this->assertEquals(
            ['tile_id', 'tile_x', 'tile_y', 'tile_far', 'tile_revealed', 'tile_disabled', 'tile_terrain'],
            $this->extractFields($mapped, 'dbName')
        );

        $this->assertEquals(
            ['int', 'int', 'int', 'int', 'bool', 'bool', Terrain::class],
            $this->extractFields($mapped, 'type')
        );

        $this->assertEquals('code', $mapped['terrain']->entityKey);
    }

    public function testCardRepositoryMetadata()
    {
        $cardClasses = [
            Lineage::class => [
                'table' => 'lineage',
                'mapped' => ['objectiveCompleted', 'leader'],
                'dbName' => ['card_completed', 'card_leader'],
                'type' => ['bool', 'bool']
            ],
            Objective::class => [
                'table' => 'objective',
                'mapped' => ['completed'],
                'dbName' => ['card_completed'],
                'type' => ['bool']
            ],
            Spell::class => [
                'table' => 'spell',
                'mapped' => ['activated'],
                'dbName' => ['card_activated'],
                'type' => ['bool']
            ],
            Invention::class => [
                'table' => 'invention',
                'mapped' => ['activated'],
                'dbName' => ['card_activated'],
                'type' => ['bool']
            ],
            Fight::class => [
                'table' => 'explore_fight',
                'mapped' => [],
                'dbName' => [],
                'type' => []
            ],
            Other::class => [
                'table' => 'explore_other',
                'mapped' => [],
                'dbName' => [],
                'type' => []
            ],
            Disease::class => [
                'table' => 'explore_disease',
                'mapped' => [],
                'dbName' => [],
                'type' => []
            ],
        ];

        foreach ($cardClasses as $cardClass => $testData) {
            $reflexion = $this->initRepository($cardClass);

            $classProp = $reflexion->getProperty('class');
            $classProp->setAccessible(true);
            $this->assertEquals($cardClass, $classProp->getValue($this->repository));

            $tableProp = $reflexion->getProperty('table');
            $tableProp->setAccessible(true);
            $this->assertEquals($testData['table'], $tableProp->getValue($this->repository));

            $keysProp = $reflexion->getProperty('keys');
            $keysProp->setAccessible(true);
            $this->assertEquals(['type', 'type_arg'], $keysProp->getValue($this->repository));

            $mappedProp = $reflexion->getProperty('mappedFields');
            $mappedProp->setAccessible(true);
            $mapped = $mappedProp->getValue($this->repository);
            $this->assertEquals(array_merge($testData['mapped'], ['type', 'type_arg', 'location', 'location_arg']), array_keys($mapped));

            foreach ($mapped as $field) {
                $this->assertInstanceOf(Field::class, $field);
            }

            $this->assertEquals(
                array_merge($testData['dbName'], ['card_type', 'card_type_arg', 'card_location', 'card_location_arg']),
                $this->extractFields($mapped, 'dbName'),
            );

            $this->assertEquals(
                array_merge($testData['type'], ['string', 'int', 'string', 'int']),
                $this->extractFields($mapped, 'type'),
            );
        }
    }

    public function testQueryBuilderUpdateTile()
    {
        $this->initRepository(Tile::class);
        $tile = new Tile(1, 0, 0, 0, false, true);
        $this->repository->update($tile, ['x', 'y', 'howFar', 'disabled', 'flip']);
        $this->assertEquals(
            'UPDATE `map` SET `tile_x` = 0, `tile_y` = 0, `tile_far` = 0, `tile_disabled` = 0, `tile_revealed` = 1 WHERE `tile_id` = 1',
            $this->repository->getLastQuery()
        );
    }

    public function testUpdateCardWithIds()
    {
        $this->repository = new class(Lineage::class) extends AbstractRepository {
            public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array {return [1, 2];}
        };

        $lineage = (new Lineage(Meeple::HUMANI_MAGE))
            ->setObjectiveCompleted(true)
        ;
        $this->repository->updateCardWithIds($lineage);

        $this->assertEquals([1, 2], $lineage->getIds());
    }

    protected function initRepository(string $class): \ReflectionObject
    {
        $this->repository = new class($class) extends AbstractRepository {};

        return new \ReflectionObject($this->repository);
    }

    protected function extractFields(array $mapped, $fieldName): array
    {
        return array_values(
            array_map(
                function(Field $field) use ($fieldName) {
                    return $field->$fieldName;
                },
                $mapped
            )
        );
    }
}