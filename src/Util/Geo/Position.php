<?php namespace MapGuesser\Util\Geo;

class Position
{
    const EARTH_RADIUS_IN_METER = 6371000;

    private float $lat;
    private float $lng;

    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function setLat(float $lat): void
    {
        $this->lat = $lat;
    }

    public function setLng(float $lng): void
    {
        $this->lng = $lng;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function calculateDistanceTo(Position $otherPosition): float
    {
        $lat1 = deg2rad($this->lat);
        $lng1 = deg2rad($this->lng);
        $lat2 = deg2rad($otherPosition->lat);
        $lng2 = deg2rad($otherPosition->lng);

        $angleCos = cos($lat1) * cos($lat2) * cos($lng2 - $lng1) + sin($lat1) * sin($lat2);

        if ($angleCos > 1.0) {
            $angleCos = 1.0;
        }

        $angle = acos($angleCos);

        return $angle * static::EARTH_RADIUS_IN_METER;
    }

    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
