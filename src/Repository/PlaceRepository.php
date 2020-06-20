<?php namespace MapGuesser\Repository;

use MapGuesser\Database\Query\Select;
use MapGuesser\PersistentData\Model\Place;
use MapGuesser\PersistentData\PersistentDataManager;

class PlaceRepository
{
    private PersistentDataManager $pdm;

    public function __construct()
    {
        $this->pdm = new PersistentDataManager();
    }

    public function getById(int $placeId): ?Place
    {
        return $this->pdm->selectFromDbById($placeId, Place::class);
    }

    public function getRandomForMapWithValidPano(int $mapId, array $exclude = [], array &$placesWithoutPano): Place
    {
        $placesWithoutPano = [];

        do {
            $place = $this->selectRandomFromDbForMap($mapId, $exclude);

            $panoId = $place->getFreshPanoId();

            if ($panoId === null) {
                $placesWithoutPano[] = $exclude[] = $place->getId();
            }
        } while ($panoId === null);

        return $place;
    }

    private function selectRandomFromDbForMap(int $mapId, array $exclude): ?Place
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->where('id', 'NOT IN', $exclude);
        $select->where('map_id', '=', $mapId);

        $numberOfPlaces = $select->count(); // TODO: what if 0
        $randomOffset = random_int(0, $numberOfPlaces - 1);

        $select->orderBy('id');
        $select->limit(1, $randomOffset);

        return $this->pdm->selectFromDb($select, Place::class);
    }
}
