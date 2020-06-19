<?php namespace MapGuesser\PersistentData\Model;

use DateInterval;
use DateTime;
use MapGuesser\Http\Request;
use MapGuesser\PersistentData\PersistentDataManager;
use MapGuesser\Util\Geo\Position;

class Place extends Model
{
    protected static string $table = 'places';

    protected static array $fields = ['map_id', 'lat', 'lng', 'pano_id_cached', 'pano_id_cached_timestamp'];

    protected static array $relations = ['map' => Map::class];

    private ?Map $map = null;

    private ?int $mapId = null;

    private Position $position;

    private ?string $panoIdCached = null;

    private ?DateTime $panoIdCachedTimestamp = null;

    public function __construct()
    {
        $this->position = new Position(0.0, 0.0);
    }

    public function setMap(Map $map): void
    {
        $this->map = $map;
    }

    public function setMapId(int $mapId): void
    {
        $this->mapId = $mapId;
    }

    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }

    public function setLat(float $lat): void
    {
        $this->position->setLat($lat);
    }

    public function setLng(float $lng): void
    {
        $this->position->setLng($lng);
    }

    public function setPanoIdCached(?string $panoIdCached): void
    {
        $this->panoIdCached = $panoIdCached;
    }

    public function setPanoIdCachedTimestampDate(?DateTime $panoIdCachedTimestamp): void
    {
        $this->panoIdCachedTimestamp = $panoIdCachedTimestamp;
    }

    public function setPanoIdCachedTimestamp(?string $panoIdCachedTimestamp): void
    {
        if ($panoIdCachedTimestamp !== null) {
            $this->panoIdCachedTimestamp = new DateTime($panoIdCachedTimestamp);
        }
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function getMapId(): ?int
    {
        return $this->mapId;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getLat(): float
    {
        return $this->position->getLat();
    }

    public function getLng(): float
    {
        return $this->position->getLng();
    }

    public function getFreshPanoId(bool $canBeIndoor = false): ?string
    {
        if (
            $this->panoIdCachedTimestamp !== null &&
            (clone $this->panoIdCachedTimestamp)->add(new DateInterval('P1D')) > new DateTime()
        ) {
            return $this->panoIdCached;
        }

        $request = new Request('https://maps.googleapis.com/maps/api/streetview/metadata', Request::HTTP_GET);
        $request->setQuery([
            'key' => $_ENV['GOOGLE_MAPS_SERVER_API_KEY'],
            'location' => $this->position->getLat() . ',' . $this->position->getLng(),
            'source' => $canBeIndoor ? 'default' : 'outdoor'
        ]);

        $response = $request->send();
        $panoData = json_decode($response->getBody(), true);
        $panoId = $panoData['status'] === 'OK' ? $panoData['pano_id'] : null;

        // enable indoor panos if no outdoor found
        if ($panoId === null && !$canBeIndoor) {
            return $this->getFreshPanoId(true);
        }

        $this->panoIdCached = $panoId;
        $this->panoIdCachedTimestamp = new DateTime('now');

        (new PersistentDataManager())->saveToDb($this);

        return $panoId;
    }

    public function getPanoIdCached(): ?string
    {
        return $this->panoIdCached;
    }

    public function getPanoIdCachedTimestampDate(): ?DateTime
    {
        return $this->panoIdCachedTimestamp;
    }

    public function getPanoIdCachedTimestamp(): ?string
    {
        if ($this->panoIdCachedTimestamp === null) {
            return null;
        }

        return $this->panoIdCachedTimestamp->format('Y-m-d H:i:s');
    }
}
