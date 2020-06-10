<?php namespace MapGuesser\Controller;

use DateTime;
use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Authentication\IUser;
use MapGuesser\Interfaces\Authorization\ISecured;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Repository\MapRepository;
use MapGuesser\Repository\PlaceRepository;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Util\Geo\Position;

class MapAdminController implements ISecured
{
    private static string $unnamedMapName = '[unnamed map]';

    private IRequest $request;

    private MapRepository $mapRepository;

    private PlaceRepository $placeRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->mapRepository = new MapRepository();
        $this->placeRepository = new PlaceRepository();
    }

    public function authorize(): bool
    {
        $user = $this->request->user();

        return $user !== null && $user->hasPermission(IUser::PERMISSION_ADMIN);
    }

    public function getMapEditor(): IContent
    {
        $mapId = (int) $this->request->query('mapId');

        if ($mapId) {
            $map = $this->mapRepository->getById($mapId);
            $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);
            $places = $this->getPlaces($mapId);
        } else {
            $map = [
                'name' => self::$unnamedMapName,
                'description' => ''
            ];
            $bounds = Bounds::createDirectly(-90.0, -180.0, 90.0, 180.0);
            $places = [];
        }

        $data = ['mapId' => $mapId, 'mapName' => $map['name'], 'mapDescription' => str_replace('<br>', "\n", $map['description']), 'bounds' => $bounds->toArray(), 'places' => &$places];
        return new HtmlContent('admin/map_editor', $data);
    }

    public function getPlace(): IContent
    {
        $placeId = (int) $this->request->query('placeId');

        $placeData = $this->placeRepository->getById($placeId);

        $data = ['panoId' => $placeData['panoId']];
        return new JsonContent($data);
    }

    public function saveMap(): IContent
    {
        $mapId = (int) $this->request->query('mapId');

        if (!$mapId) {
            $mapId = $this->addNewMap();
        }

        if (isset($_POST['added'])) {
            $addedIds = [];
            foreach ($_POST['added'] as $placeRaw) {
                $placeRaw = json_decode($placeRaw, true);

                $addedIds[] = ['tempId' => $placeRaw['id'], $this->placeRepository->addToMap($mapId, [
                    'lat' => (float) $placeRaw['lat'],
                    'lng' => (float) $placeRaw['lng'],
                    'pano_id_cached_timestamp' => $placeRaw['panoId'] === -1 ? (new DateTime('-1 day'))->format('Y-m-d H:i:s') : null
                ])];
            }
        } else {
            $addedIds = [];
        }

        if (isset($_POST['edited'])) {
            foreach ($_POST['edited'] as $placeRaw) {
                $placeRaw = json_decode($placeRaw, true);

                $this->placeRepository->modify((int) $placeRaw['id'], [
                    'lat' => (float) $placeRaw['lat'],
                    'lng' => (float) $placeRaw['lng']
                ]);
            }
        }

        if (isset($_POST['deleted'])) {
            foreach ($_POST['deleted'] as $placeRaw) {
                $placeRaw = json_decode($placeRaw, true);

                $this->placeRepository->delete($placeRaw['id']);
            }
        }

        $mapBounds = $this->calculateMapBounds($mapId);

        $map = [
            'bound_south_lat' => $mapBounds->getSouthLat(),
            'bound_west_lng' => $mapBounds->getWestLng(),
            'bound_north_lat' => $mapBounds->getNorthLat(),
            'bound_east_lng' => $mapBounds->getEastLng()
        ];

        if (isset($_POST['name'])) {
            $map['name'] = $_POST['name'] ? $_POST['name'] : self::$unnamedMapName;
        }
        if (isset($_POST['description'])) {
            $map['description'] = str_replace(["\n", "\r\n"], '<br>', $_POST['description']);
        }

        $this->saveMapData($mapId, $map);

        $data = ['mapId' => $mapId, 'added' => $addedIds];
        return new JsonContent($data);
    }

    private function calculateMapBounds(int $mapId): Bounds
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['lat', 'lng']);
        $select->where('map_id', '=', $mapId);

        $result = $select->execute();

        $bounds = new Bounds();
        while ($place = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $bounds->extend(new Position($place['lat'], $place['lng']));
        }

        return $bounds;
    }

    private function addNewMap(): int
    {
        $modify = new Modify(\Container::$dbConnection, 'maps');
        $modify->fill([
            'name' => self::$unnamedMapName,
            'description' => ''
        ]);
        $modify->save();

        return $modify->getId();
    }

    private function saveMapData(int $mapId, array $map): void
    {
        $modify = new Modify(\Container::$dbConnection, 'maps');
        $modify->setId($mapId);
        $modify->fill($map);
        $modify->save();
    }

    private function &getPlaces(int $mapId): array
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['id', 'lat', 'lng', 'pano_id_cached', 'pano_id_cached_timestamp']);
        $select->where('map_id', '=', $mapId);

        $result = $select->execute();

        $places = [];

        while ($place = $result->fetch(IResultSet::FETCH_ASSOC)) {
            //$panoId = ???
            //$pov = ???
            $noPano = $place['pano_id_cached_timestamp'] && $place['pano_id_cached'] === null;

            $places[$place['id']] = [
                'id' => $place['id'],
                'lat' => $place['lat'],
                'lng' => $place['lng'],
                'panoId' => null,
                'pov' => ['heading' => 0.0, 'pitch' => 0.0, 'zoom' => 0.0],
                'noPano' => $noPano
            ];
        }

        return $places;
    }
}
