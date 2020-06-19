<?php namespace MapGuesser\Controller;

use DateTime;
use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Authentication\IUser;
use MapGuesser\Interfaces\Authorization\ISecured;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\PersistentData\Model\Map;
use MapGuesser\PersistentData\PersistentDataManager;
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

    private PersistentDataManager $pdm;

    private MapRepository $mapRepository;

    private PlaceRepository $placeRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->pdm = new PersistentDataManager();
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
            $places = $this->getPlaces($mapId);
        } else {
            $map = new Map();
            $map->setName(self::$unnamedMapName);
            $places = [];
        }

        $data = ['mapId' => $mapId, 'mapName' => $map->getName(), 'mapDescription' => str_replace('<br>', "\n", $map->getDescription()), 'bounds' => $map->getBounds()->toArray(), 'places' => &$places];
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

        \Container::$dbConnection->startTransaction();

        if ($mapId) {
            $map = $this->mapRepository->getById($mapId);
        } else {
            $map = new Map();
            $map->setName(self::$unnamedMapName);
            $this->pdm->saveToDb($map);
            $mapId = $map->getId();
        }

        if (isset($_POST['added'])) {
            $addedIds = [];
            foreach ($_POST['added'] as $placeRaw) {
                $placeRaw = json_decode($placeRaw, true);

                $addedIds[] = ['tempId' => $placeRaw['id'], 'id' => $this->placeRepository->addToMap($mapId, [
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

        $map->setBounds($mapBounds);
        $map->setArea($mapBounds->calculateApproximateArea());

        if (isset($_POST['name'])) {
            $map->setName($_POST['name'] ? $_POST['name'] : self::$unnamedMapName);
        }
        if (isset($_POST['description'])) {
            $map->setDescription(str_replace(["\n", "\r\n"], '<br>', $_POST['description']));
        }

        $this->pdm->saveToDb($map);

        \Container::$dbConnection->commit();

        $data = ['mapId' => $mapId, 'added' => $addedIds];
        return new JsonContent($data);
    }

    public function deleteMap() {
        $mapId = (int) $this->request->query('mapId');

        $map = $this->mapRepository->getById($mapId);

        \Container::$dbConnection->startTransaction();

        $this->deletePlaces($mapId);

        $this->pdm->deleteFromDb($map);

        \Container::$dbConnection->commit();

        $data = ['success' => true];
        return new JsonContent($data);
    }

    private function deletePlaces(int $mapId): void
    {
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['id']);
        $select->where('map_id', '=', $mapId);

        $result = $select->execute();

        while ($place = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $modify = new Modify(\Container::$dbConnection, 'places');
            $modify->setId($place['id']);
            $modify->delete();
        }
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
