<?php namespace MapGuesser\Repository;

use MapGuesser\Util\Geo\Position;
use MapGuesser\Database\Query\Select;
use MapGuesser\Database\Query\Modify;
use MapGuesser\Http\Request;
use MapGuesser\Interfaces\Database\IResultSet;
use DateTime;
use DateInterval;

class PlaceRepository
{
    public function getById(int $placeId)
    {
        $place = $this->selectFromDbById($placeId);

        $panoId = $this->requestPanoId($place);

        $position = new Position($place['lat'], $place['lng']);

        return [
            'position' => $position,
            'panoId' => $panoId
        ];
    }

    public function getForMapWithValidPano(int $mapId, array $exclude = []): array
    {
        $placesWithoutPano = [];

        do {
            $place = $this->selectFromDbForMap($mapId, $exclude);

            $panoId = $this->requestPanoId($place);

            if ($panoId === null) {
                $placesWithoutPano[] = $place['id'];
            }
        } while ($panoId === null);

        $position = new Position($place['lat'], $place['lng']);

        return [
            'placesWithoutPano' => $placesWithoutPano,
            'placeId' => $place['id'],
            'position' => $position,
            'panoId' => $panoId
        ];
    }

    private function selectFromDbById(int $placeId): array
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['id', 'lat', 'lng', 'pano_id_cached', 'pano_id_cached_timestamp']);
        $select->whereId($placeId);

        $place = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        return $place;
    }

    private function selectFromDbForMap(int $mapId, array $exclude): array
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['id', 'lat', 'lng', 'pano_id_cached', 'pano_id_cached_timestamp']);
        $select->where('id', 'NOT IN', $exclude);
        $select->where('map_id', '=', $mapId);

        $numberOfPlaces = $select->count();// TODO: what if 0
        $randomOffset = random_int(0, $numberOfPlaces - 1);

        $select->orderBy('id');
        $select->limit(1, $randomOffset);

        $place = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        return $place;
    }

    private function requestPanoId(array $place): ?string
    {
        if (
            $place['pano_id_cached_timestamp'] &&
            (new DateTime($place['pano_id_cached_timestamp']))->add(new DateInterval('P1D')) > new DateTime()
        ) {
            return $place['pano_id_cached'];
        }

        $request = new Request('https://maps.googleapis.com/maps/api/streetview/metadata', Request::HTTP_GET);
        $request->setQuery([
            'key' => $_ENV['GOOGLE_MAPS_SERVER_API_KEY'],
            'location' => $place['lat'] . ',' . $place['lng'],
            'source' => 'outdoor'
        ]);

        $response = $request->send();

        $panoData = json_decode($response->getBody(), true);

        $panoId = $panoData['status'] === 'OK' ? $panoData['pano_id'] : null;

        $this->saveCachedPanoId($place['id'], $panoId);

        return $panoId;
    }

    private function saveCachedPanoId(int $placeId, ?string $panoId): void
    {
        $modify = new Modify(\Container::$dbConnection, 'places');
        $modify->setId($placeId);
        $modify->set('pano_id_cached', $panoId);
        $modify->set('pano_id_cached_timestamp', (new DateTime())->format('Y-m-d H:i:s'));
        $modify->save();
    }
}
