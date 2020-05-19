<?php namespace MapGuesser\Util\Geo;

class Bounds
{
    private float $southLat;
    private float $westLng;

    private float $northLat;
    private float $eastLng;

    private bool $initialized = false;

    public static function createWithPosition(Position $position): Bounds
    {
        $instance = new static();

        $instance->initialize($position);

        return $instance;
    }

    public static function createDirectly(float $southLat, $westLng, $northLat, $eastLng): Bounds
    {
        $instance = new static();

        $instance->southLat = $southLat;
        $instance->westLng = $westLng;
        $instance->northLat = $northLat;
        $instance->eastLng = $eastLng;

        $instance->initialized = true;

        return $instance;
    }

    public function extend(Position $position): void
    {
        if (!$this->initialized) {
            $this->initialize($position);

            return;
        }

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

    public function toJson(): string
    {
        if (!$this->initialized) {
            throw new \Exception("Bounds are not initialized!");
        }

        return json_encode([
            'south' => $this->southLat,
            'west' => $this->westLng,
            'north' => $this->northLat,
            'east' => $this->eastLng,
        ]);
    }

    private function initialize(Position $position)
    {
        $lat = $position->getLat();
        $lng = $position->getLng();

        $this->northLat = $lat;
        $this->westLng = $lng;
        $this->southLat = $lat;
        $this->eastLng = $lng;

        $this->initialized = true;
    }
}
