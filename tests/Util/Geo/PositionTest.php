<?php namespace MapGuesser\Tests\Util\Geo;

use MapGuesser\Util\Geo\Position;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    public function testCanBeCreatedWithLatAndLng(): void
    {
        $position = new Position(45.25, 18.12);

        $this->assertEquals(45.25, $position->getLat());
        $this->assertEquals(18.12, $position->getLng());
    }

    public function testCanCalculateDistanceToAnotherPosition(): void
    {
        $position1 = new Position(44.12, 7.33);
        $position2 = new Position(50.12, 15.11);

        $this->assertEqualsWithDelta(888785.73, $position1->calculateDistanceTo($position2), 0.01);
    }

    public function testCanBeConvertedToArray(): void
    {
        $position = new Position(45.25, 18.12);

        $this->assertEquals(
            ['lat' => 45.25, 'lng' => 18.12],
            $position->toArray()
        );
    }
}
