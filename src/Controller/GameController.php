<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Repository\MapRepository;

class GameController
{
    private IRequest $request;

    private MapRepository $mapRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->mapRepository = new MapRepository();
    }

    public function getGame(): IContent
    {
        $mapId = (int) $this->request->query('mapId');
        $data = $this->prepareGame($mapId);
        return new HtmlContent('game', $data);
    }

    public function getGameJson(): IContent
    {
        $mapId = (int) $this->request->query('mapId');
        $data = $this->prepareGame($mapId);
        return new JsonContent($data);
    }

    private function prepareGame(int $mapId)
    {
        $map = $this->mapRepository->getById($mapId);

        $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

        $session = $this->request->session();

        if (($state = $session->get('state')) && $state['mapId'] !== $mapId) {
            $session->set('state', [
                'mapId' => $mapId,
                'area' => $bounds->calculateApproximateArea(),
                'rounds' => []
            ]);
        }

        return ['mapId' => $mapId, 'mapName' => $map['name'], 'bounds' => $bounds->toArray()];
    }
}
