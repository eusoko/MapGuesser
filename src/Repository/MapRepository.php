<?php namespace MapGuesser\Repository;

use MapGuesser\PersistentData\Model\Map;
use MapGuesser\PersistentData\PersistentDataManager;

class MapRepository
{
    private PersistentDataManager $pdm;

    public function __construct()
    {
        $this->pdm = new PersistentDataManager();
    }

    public function getById(int $mapId): ?Map
    {
        return $this->pdm->selectFromDbById($mapId, Map::class);
    }
}
