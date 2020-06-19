<?php namespace MapGuesser\Util\Geo;

class Bounds
{
    const ONE_DEGREE_OF_LATITUDE_IN_KM = 111.132954;

    private float $southLat = 90.0;
    private float $westLng = 180.0;

    private float $northLat = -90.0;
    private float $eastLng = -180.0;

    public function __construct(Position $position = null)
    {
        if ($position === null) {
            return;
        }

        $lat = $position->getLat();
        $lng = $position->getLng();

        $this->northLat = $lat;
        $this->westLng = $lng;
        $this->southLat = $lat;
        $this->eastLng = $lng;
    }

    public static function createDirectly(float $southLat, float $westLng, float $northLat, float $eastLng): Bounds
    {
        $instance = new static();

        $instance->southLat = $southLat;
        $instance->westLng = $westLng;
        $instance->northLat = $northLat;
        $instance->eastLng = $eastLng;

        return $instance;
    }

    public function extend(Position $position): void
    {
        $lat = $position->getLat();
        $lng = $position->getLng();

        if ($lat < $this->southLat) {
            $this->southLat = $lat;
        }

        if ($lng < $this->westLng) {
            $this->westLng = $lng;
        }

        if ($lat > $this->northLat) {
            $this->northLat = $lat;
        }

        if ($lng > $this->eastLng) {
            $this->eastLng = $lng;
        }
    }

    public function setSouthLat(float $southLat): void
    {
        $this->southLat = $southLat;
    }

    public function setWestLng(float $westLng): void
    {
        $this->westLng = $westLng;
    }

    public function setNorthLat(float $northLat): void
    {
        $this->northLat = $northLat;
    }

    public function setEastLng(float $eastLng): void
    {
        $this->eastLng = $eastLng;
    }

    public function getSouthLat(): float
    {
        return $this->southLat;
    }

    public function getWestLng(): float
    {
        return $this->westLng;
    }

    public function getNorthLat(): float
    {
        return $this->northLat;
    }

    public function getEastLng(): float
    {
        return $this->eastLng;
    }

    public function calculateApproximateArea(): float
    {
        $dLat = $this->northLat - $this->southLat;
        $dLng = $this->eastLng - $this->westLng;

        $m = $dLat * static::ONE_DEGREE_OF_LATITUDE_IN_KM;
        $a = $dLng * static::ONE_DEGREE_OF_LATITUDE_IN_KM * cos(deg2rad($this->northLat));
        $c = $dLng * static::ONE_DEGREE_OF_LATITUDE_IN_KM * cos(deg2rad($this->southLat));

        return $m * ($a + $c) / 2;
    }

    public function toArray(): array
    {
        return [
            'south' => $this->southLat,
            'west' => $this->westLng,
            'north' => $this->northLat,
            'east' => $this->eastLng,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
