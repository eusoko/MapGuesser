<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Interfaces\Response\IContent;

class GameController
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
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
        $bounds = $this->getMapBounds($mapId);

        $session = $this->request->session();

        if (($state = $session->get('state')) && $state['mapId'] !== $mapId) {
            $session->set('state', [
                'mapId' => $mapId,
                'area' => $bounds->calculateApproximateArea(),
                'rounds' => []
            ]);
        }

        return ['mapId' => $mapId, 'bounds' => $bounds->toArray()];
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
}
