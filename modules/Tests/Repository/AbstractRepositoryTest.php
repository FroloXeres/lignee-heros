<?php

namespace LdH\Tests\Repository;

use LdH\Entity\Cards\AbstractCard;
use LdH\Entity\Map\Terrain;
use LdH\Entity\Map\Tile;
use LdH\Repository\AbstractRepository;
use LdH\Repository\Field;
use PHPUnit\Framework\TestCase;

require_once "./../Dummy/APP_DbObject.php";

class AbstractRepositoryTest extends TestCase
{
    protected AbstractRepository $mapRepository;
    protected AbstractRepository $cardRepository;

    public function testMapRepositoryMetadata()
    {
        $this->mapRepository = new class(Tile::class) extends AbstractRepository {};
        $reflexion = new \ReflectionObject($this->mapRepository);

        $classProp = $reflexion->getProperty('class');
        $classProp->setAccessible(true);
        $this->assertEquals($classProp->getValue($this->mapRepository), Tile::class);

        $tableProp = $reflexion->getProperty('table');
        $tableProp->setAccessible(true);
        $this->assertEquals($tableProp->getValue($this->mapRepository), 'map');

        $keysProp = $reflexion->getProperty('keys');
        $keysProp->setAccessible(true);
        $this->assertEquals($keysProp->getValue($this->mapRepository), ['id']);

        $mappedProp = $reflexion->getProperty('mappedFields');
        $mappedProp->setAccessible(true);
        $mapped = $mappedProp->getValue($this->mapRepository);
        $this->assertEquals(array_keys($mapped), ['id', 'x', 'y', 'howFar', 'flip', 'disabled', 'terrain']);

        foreach ($mapped as $field) {
            $this->assertInstanceOf(Field::class, $field);
        }

        $this->assertEquals(
            $this->extractFields($mapped, 'dbName'),
            ['tile_id', 'tile_x', 'tile_y', 'tile_far', 'tile_revealed', 'tile_disabled', 'tile_terrain']
        );

        $this->assertEquals(
            $this->extractFields($mapped, 'type'),
            ['int', 'int', 'int', 'int', 'bool', 'bool', Terrain::class]
        );

        $this->assertEquals($mapped['terrain']->entityKey, 'code');
    }

    public function testCardRepositoryMetadata()
    {
        $this->cardRepository = new class(AbstractCard::class) extends AbstractRepository {};
        $reflexion = new \ReflectionObject($this->cardRepository);

        $classProp = $reflexion->getProperty('class');
        $classProp->setAccessible(true);
        $this->assertEquals($classProp->getValue($this->mapRepository), Tile::class);

        $tableProp = $reflexion->getProperty('table');
        $tableProp->setAccessible(true);
        $this->assertEquals($tableProp->getValue($this->mapRepository), 'map');

        $keysProp = $reflexion->getProperty('keys');
        $keysProp->setAccessible(true);
        $this->assertEquals($keysProp->getValue($this->mapRepository), ['id']);

        $mappedProp = $reflexion->getProperty('mappedFields');
        $mappedProp->setAccessible(true);
        $mapped = $mappedProp->getValue($this->mapRepository);
        $this->assertEquals(array_keys($mapped), ['id', 'x', 'y', 'howFar', 'flip', 'disabled', 'terrain']);

        foreach ($mapped as $field) {
            $this->assertInstanceOf(Field::class, $field);
        }

        $this->assertEquals(
            $this->extractFields($mapped, 'dbName'),
            ['tile_id', 'tile_x', 'tile_y', 'tile_far', 'tile_revealed', 'tile_disabled', 'tile_terrain']
        );

        $this->assertEquals(
            $this->extractFields($mapped, 'type'),
            ['int', 'int', 'int', 'int', 'bool', 'bool', Terrain::class]
        );

        $this->assertEquals($mapped['terrain']->entityKey, 'code');
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