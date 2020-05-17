<?php namespace MapGuesser\Util\Geo;

class Position
{
    private float $lat;
    private float $lng;

    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
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

    public function toJson(): string
    {
        return json_encode([
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);
    }
}
