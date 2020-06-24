<?php namespace MapGuesser\Tests\Util\Geo;

use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Util\Geo\Position;
use PHPUnit\Framework\TestCase;

final class BoundsTest extends TestCase
{
    public function testCanBeCreatedEmpty(): void
    {
        $bounds = new Bounds();

        $this->assertEquals(90.0, $bounds->getSouthLat());
        $this->assertEquals(180.0, $bounds->getWestLng());
        $this->assertEquals(-90.0, $bounds->getNorthLat());
        $this->assertEquals(-180.0, $bounds->getEastLng());
    }

    public function testCanBeCreatedWithPosition(): void
    {
        $position = new Position(45.25, 18.12);

        $bounds = new Bounds($position);

        $this->assertEquals($position->getLat(), $bounds->getSouthLat());
        $this->assertEquals($position->getLng(), $bounds->getWestLng());
        $this->assertEquals($position->getLat(), $bounds->getNorthLat());
        $this->assertEquals($position->getLng(), $bounds->getEastLng());
    }

    public function testCanBeCreatedDirectly(): void
    {
        $bounds = Bounds::createDirectly(44.12, 7.33, 50.12, 15.11);

        $this->assertEquals(44.12, $bounds->getSouthLat());
        $this->assertEquals(7.33, $bounds->getWestLng());
        $this->assertEquals(50.12, $bounds->getNorthLat());
        $this->assertEquals(15.11, $bounds->getEastLng());
    }

    public function testExtendingWithOnePositionIsTheSameAsCreatingWithPosition(): void
    {
        $position = new Position(45.25, 18.12);

        $bounds1 = new Bounds();
        $bounds1->extend($position);

        $bounds2 = new Bounds($position);

        $this->assertEquals($bounds1, $bounds2);
    }

    public function testCanBeExtendedWithTwoPositions(): void
    {
        $bounds = new Bounds();

        $position1 = new Position(45.25, 18.12);
        $position2 = new Position(43.15, 19.28);

        $bounds->extend($position1);
        $bounds->extend($position2);

        $this->assertEquals($position2->getLat(), $bounds->getSouthLat());
        $this->assertEquals($position1->getLng(), $bounds->getWestLng());
        $this->assertEquals($position1->getLat(), $bounds->getNorthLat());
        $this->assertEquals($position2->getLng(), $bounds->getEastLng());

        $bounds = new Bounds();

        $position1 = new Position(43.15, 18.12);
        $position2 = new Position(45.25, 19.28);

        $bounds->extend($position1);
        $bounds->extend($position2);

        $this->assertEquals($position1->getLat(), $bounds->getSouthLat());
        $this->assertEquals($position1->getLng(), $bounds->getWestLng());
        $this->assertEquals($position2->getLat(), $bounds->getNorthLat());
        $this->assertEquals($position2->getLng(), $bounds->getEastLng());
    }

    public function testCanCalculateApproximateArea(): void
    {
        $bounds = Bounds::createDirectly(44.12, 7.33, 50.12, 15.11);

        $this->assertEqualsWithDelta(391766.09, $bounds->calculateApproximateArea(), 0.01);
    }

    public function testCanBeConvertedToArray(): void
    {
        $bounds = Bounds::createDirectly(44.12, 7.33, 50.12, 15.11);

        $this->assertEquals(
            ['south' => 44.12, 'west' => 7.33, 'north' => 50.12, 'east' => 15.11],
            $bounds->toArray()
        );
    }
}
