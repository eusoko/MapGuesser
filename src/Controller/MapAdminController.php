<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Repository\PlaceRepository;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Util\Geo\Bounds;

class MapAdminController
{
    private PlaceRepository $placeRepository;

    public function __construct()
    {
        $this->placeRepository = new PlaceRepository();
    }

    public function getMaps(): IContent
    {
        //TODO

        return new HtmlContent('admin/maps');
    }

    public function getMapEditor(array $parameters): IContent
    {
        $mapId = (int) $parameters['mapId'];

        $bounds = $this->getMapBounds($mapId);

        $places = $this->getPlaces($mapId);

        $data = ['mapId' => $mapId, 'bounds' => $bounds->toArray(), 'places' => &$places];
        return new HtmlContent('admin/map_editor', $data);
    }

    public function getPlace(array $parameters)
    {
        $placeId = (int) $parameters['placeId'];

        $placeData = $this->placeRepository->getById($placeId);

        $data = ['panoId' => $placeData['panoId']];
        return new JsonContent($data);
    }

    private function getMapBounds(int $mapId): Bounds
    {
        $select = new Select(\Container::$dbConnection, 'maps');
        $select->columns(['bound_south_lat', 'bound_west_lng', 'bound_north_lat', 'bound_east_lng']);
        $select->whereId($mapId);

        $map = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

        return $bounds;
    }

    private function &getPlaces(int $mapId): array
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['id', 'lat', 'lng', 'pano_id_cached', 'pano_id_cached_timestamp']);
        $select->where('map_id', '=', $mapId);
        $select->orderBy('lng');

        $result = $select->execute();

        $places = [];

        while ($place = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $noPano = $place['pano_id_cached_timestamp'] && $place['pano_id_cached'] === null;

            $places[] = ['id' => $place['id'], 'lat' => $place['lat'], 'lng' => $place['lng'], 'noPano' => $noPano];
        }

        return $places;
    }
}
