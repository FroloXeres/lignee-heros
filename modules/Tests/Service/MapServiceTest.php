<?php

namespace LdH\Tests\Service;

use LdH\Object\Coordinate;
use LdH\Service\MapService;
use PHPUnit\Framework\TestCase;


class MapServiceTest extends TestCase
{
    public function testDistanceFromCenter()
    {
        $this->assertEquals(0, MapService::getDistanceFromCenter(0, 0));

        $this->assertEquals(1, MapService::getDistanceFromCenter(-1, 0));
        $this->assertEquals(1, MapService::getDistanceFromCenter(0, -1));
        $this->assertEquals(1, MapService::getDistanceFromCenter(1, -1));
        $this->assertEquals(1, MapService::getDistanceFromCenter(-1, 1));
        $this->assertEquals(1, MapService::getDistanceFromCenter(1, 0));
        $this->assertEquals(1, MapService::getDistanceFromCenter(0, 1));

        $this->assertEquals(2, MapService::getDistanceFromCenter(-1, -1));
        $this->assertEquals(2, MapService::getDistanceFromCenter(0, -2));
        $this->assertEquals(2, MapService::getDistanceFromCenter(1, -2));
        $this->assertEquals(2, MapService::getDistanceFromCenter(-2, 0));
        $this->assertEquals(2, MapService::getDistanceFromCenter(2, -2));
        $this->assertEquals(2, MapService::getDistanceFromCenter(-2, 1));
        $this->assertEquals(2, MapService::getDistanceFromCenter(2, -1));
        $this->assertEquals(2, MapService::getDistanceFromCenter(-2, 2));
        $this->assertEquals(2, MapService::getDistanceFromCenter(-1, 2));
        $this->assertEquals(2, MapService::getDistanceFromCenter(1, 1));
        $this->assertEquals(2, MapService::getDistanceFromCenter(0, 2));
        $this->assertEquals(2, MapService::getDistanceFromCenter(2, 0));

        $this->assertEquals(3, MapService::getDistanceFromCenter(-3, 1));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-1, 3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-2, 3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(1, 2));
        $this->assertEquals(3, MapService::getDistanceFromCenter(3, 0));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-1, -2));
        $this->assertEquals(3, MapService::getDistanceFromCenter(0, -3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(1, -3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(2, 1));
        $this->assertEquals(3, MapService::getDistanceFromCenter(0, 3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-3, 3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(3, -1));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-3, 0));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-2, -1));
        $this->assertEquals(3, MapService::getDistanceFromCenter(-3, 2));
        $this->assertEquals(3, MapService::getDistanceFromCenter(3, -2));
        $this->assertEquals(3, MapService::getDistanceFromCenter(2, -3));
        $this->assertEquals(3, MapService::getDistanceFromCenter(3, -3));

        $this->assertEquals(4, MapService::getDistanceFromCenter(-2, 4));
        $this->assertEquals(4, MapService::getDistanceFromCenter(-2, -2));
        $this->assertEquals(4, MapService::getDistanceFromCenter(-1, 4));
        $this->assertEquals(4, MapService::getDistanceFromCenter(1, 3));
        $this->assertEquals(4, MapService::getDistanceFromCenter(2, 2));
        $this->assertEquals(4, MapService::getDistanceFromCenter(3, 1));
        $this->assertEquals(4, MapService::getDistanceFromCenter(-3, 4));
        $this->assertEquals(4, MapService::getDistanceFromCenter(3, -4));
        $this->assertEquals(4, MapService::getDistanceFromCenter(2, -4));
        $this->assertEquals(4, MapService::getDistanceFromCenter(-3, -1));

        $this->assertEquals(5, MapService::getDistanceFromCenter(-3, 5));
        $this->assertEquals(5, MapService::getDistanceFromCenter(3, 2));
    }

    public function testIsTooFar()
    {
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, 0)));

        $this->assertFalse(MapService::isTooFar(new Coordinate(-1, 0)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, -1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(1, -1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-1, 1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(1, 0)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, 1)));

        $this->assertFalse(MapService::isTooFar(new Coordinate(-1, -1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, -2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(1, -2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-2, 0)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(2, -2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-2, 1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(2, -1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-2, 2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-1, 2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(1, 1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, 2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(2, 0)));

        $this->assertFalse(MapService::isTooFar(new Coordinate(-3, 1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-1, 3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-2, 3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(1, 2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(3, 0)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-1, -2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, -3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(1, -3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(2, 1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(0, 3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-3, 3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(3, -1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-3, 0)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-2, -1)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(-3, 2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(3, -2)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(2, -3)));
        $this->assertFalse(MapService::isTooFar(new Coordinate(3, -3)));

        $this->assertTrue(MapService::isTooFar(new Coordinate(-2, 4)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(-2, -2)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(-1, 4)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(1, 3)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(2, 2)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(3, 1)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(-3, 4)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(3, -4)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(2, -4)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(-3, -1)));

        $this->assertTrue(MapService::isTooFar(new Coordinate(-3, 5)));
        $this->assertTrue(MapService::isTooFar(new Coordinate(3, 2)));
    }
}