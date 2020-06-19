<?php namespace MapGuesser\PersistentData\Model;

use MapGuesser\Util\Geo\Bounds;

class Map extends Model
{
    protected static string $table = 'maps';

    protected static array $fields = ['name', 'description', 'bound_south_lat', 'bound_west_lng', 'bound_north_lat', 'bound_east_lng', 'area'];

    private string $name = '';

    private string $description = '';

    private Bounds $bounds;

    private float $area = 0.0;

    public function __construct()
    {
        $this->bounds = Bounds::createDirectly(-90.0, -180.0, 90.0, 180.0);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setBounds(Bounds $bounds): void
    {
        $this->bounds = $bounds;
    }

    public function setBoundSouthLat(float $bound_south_lat): void
    {
        $this->bounds->setSouthLat($bound_south_lat);
    }

    public function setBoundWestLng(float $bound_west_lng): void
    {
        $this->bounds->setWestLng($bound_west_lng);
    }

    public function setBoundNorthLat(float $bound_north_lat): void
    {
        $this->bounds->setNorthLat($bound_north_lat);
    }

    public function setBoundEastLng(float $bound_east_lng): void
    {
        $this->bounds->setEastLng($bound_east_lng);
    }

    public function setArea(float $area): void
    {
        $this->area = $area;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBounds(): Bounds
    {
        return $this->bounds;
    }

    public function getBoundSouthLat(): float
    {
        return $this->bounds->getSouthLat();
    }

    public function getBoundWestLng(): float
    {
        return $this->bounds->getWestLng();
    }

    public function getBoundNorthLat(): float
    {
        return $this->bounds->getNorthLat();
    }

    public function getBoundEastLng(): float
    {
        return $this->bounds->getEastLng();
    }

    public function getArea(): float
    {
        return $this->area;
    }
}
