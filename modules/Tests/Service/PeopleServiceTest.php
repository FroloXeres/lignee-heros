<?php

namespace LdH\Tests\Service;

use LdH\Object\Coordinate;
use LdH\Object\SimpleTile;
use LdH\Service\MapService;
use LdH\Service\PeopleService;
use PHPUnit\Framework\TestCase;


class PeopleServiceTest extends TestCase
{
    protected ?PeopleService $peopleService = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->peopleService = new PeopleService();
    }

    public function testPathfinder()
    {

        $tiles = self::getTiles();
        foreach (self::getOneTileChecks() as $center => $around) {
            $found = [];
            $this->peopleService->pathfinder(
                $found,
                self::getSimpleMap(),
                $tiles[$center],
                1
            );
            foreach ($found as $tile) {
                $this->assertSameSize($found, $around);
                $this->assertTrue(in_array(
                    $tile->key(),
                    $around
                ), $tile->key() . ' not found in ' . print_r($around, true));
            }
        }
        foreach (self::getTwoTileChecks() as $center => $around) {
            $found = [];
            $this->peopleService->pathfinder(
                $found,
                self::getSimpleMap(),
                $tiles[$center],
                2
            );
            foreach ($found as $tile) {
                $this->assertSameSize($around, $found, 'Pathfinding[' . $center . '] not the count');
                $this->assertTrue(in_array(
                    $tile->key(),
                    $around
                ), 'Pathfinding['.$center.'] ' . $tile->key() . ' not found in ' . print_r($around, true));
            }
        }
    }

    public function testGetAround()
    {
        $tiles = self::getTiles();
        foreach (self::getOneTileChecks() as $center => $expectedAround) {
            $around = $this->peopleService->getAround($tiles[$center]);
            $this->assertSameSize($expectedAround, $around);

            foreach ($around as $coordinate) {
                $this->assertTrue(
                    in_array(
                        $coordinate->key(),
                        $expectedAround
                    ),
                    sprintf('%s is not around %s', $coordinate->key(), $center)
                );
            }
        }
    }

    public static function getOneTileChecks(): array
    {
        return [
            '0' => ['-1_0', '0_-1', '1_-1', '-1_1', '1_0', '0_1'],
            '2k' => ['1_2', '-1_2', '0_3', '1_1', '-1_3', '0_1'],
            '3m' => ['-2_-1', '-3_1', '-2_0'],
            '3g' => ['-1_-2', '1_-3', '0_-2'],
            '3l' => ['3_-2', '2_-1', '2_0', '3_0'],
            '3c' => ['-3_3', '-2_2', '-1_2', '-1_3'],
        ];
    }



    public static function getTwoTileChecks(): array
    {
        return [
            '0' => ['-1_0', '0_-1', '1_-1', '-1_1', '1_0', '0_1', '-1_-1', '0_-2', '1_-2', '-2_0', '2_-2', '-2_1', '2_-1', '-2_2', '-1_2', '1_1', '0_2', '2_0', '0_0'],
            '2k' => ['0_0', '-1_1', '1_0', '-2_2', '-1_2', '0_1', '1_1', '2_0', '-2_3', '-1_3', '1_2', '2_1', '0_3', '0_2'],
            '3m' => ['-2_-1', '-3_1', '-2_0', '-1_-2', '-1_-1', '-1_0', '-3_2', '-2_1', '-3_0'],
            '3g' => ['-1_-2', '1_-3', '0_-2', '-2_-1', '-1_-1', '1_-2', '2_-3', '0_-1', '0_-3'],
            '3l' => ['3_-2', '2_-1', '2_0', '3_0', '3_-3', '1_-1', '2_-2', '1_0', '1_1', '2_1', '3_-1'],
            '3c' => ['-3_3', '-2_2', '-1_2', '-1_3', '-3_2', '-2_1', '-1_1', '0_1', '0_2', '0_3', '-2_3'],
        ];
    }

    public static function getSimpleMap(): array
    {
        $i = 0;
        $map = [];
        foreach (self::getTiles() as $tile) {
            $map[$tile->key()] = new SimpleTile(
               ++$i,
               $tile->x,
               $tile->y,
               true
            );
        }
        return $map;
    }

    /** @return array<Coordinate> */
    public static function getTiles(): array
    {
        return [
            '0' => new Coordinate(0, 0),

            '1a' => new Coordinate(-1, 0),
            '1b' => new Coordinate(0, -1),
            '1c' => new Coordinate(1, -1),
            '1d' => new Coordinate(-1, 1),
            '1e' => new Coordinate(1, 0),
            '1f' => new Coordinate(0, 1),

            '2a' => new Coordinate(-1, -1),
            '2b' => new Coordinate(0, -2),
            '2c' => new Coordinate(1, -2),
            '2d' => new Coordinate(-2, 0),
            '2e' => new Coordinate(2, -2),
            '2f' => new Coordinate(-2, 1),
            '2g' => new Coordinate(2, -1),
            '2h' => new Coordinate(-2, 2),
            '2i' => new Coordinate(-1, 2),
            '2j' => new Coordinate(1, 1),
            '2k' => new Coordinate(0, 2),
            '2l' => new Coordinate(2, 0),

            '3a' => new Coordinate(-3, 1),
            '3b' => new Coordinate(-1, 3),
            '3c' => new Coordinate(-2, 3),
            '3d' => new Coordinate(1, 2),
            '3e' => new Coordinate(3, 0),
            '3f' => new Coordinate(-1, -2),
            '3g' => new Coordinate(0, -3),
            '3h' => new Coordinate(1, -3),
            '3i' => new Coordinate(2, 1),
            '3j' => new Coordinate(0, 3),
            '3k' => new Coordinate(-3, 3),
            '3l' => new Coordinate(3, -1),
            '3m' => new Coordinate(-3, 0),
            '3n' => new Coordinate(-2, -1),
            '3o' => new Coordinate(-3, 2),
            '3p' => new Coordinate(3, -2),
            '3q' => new Coordinate(2, -3),
            '3r' => new Coordinate(3, -3)
        ];
    }
}