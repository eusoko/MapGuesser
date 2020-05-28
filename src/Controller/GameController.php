<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Controller\IController;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Util\Geo\Bounds;
use MapGuesser\View\HtmlView;
use MapGuesser\View\JsonView;
use MapGuesser\Interfaces\View\IView;

class GameController implements IController
{
    private bool $jsonResponse;

    // demo map
    private int $mapId = 1;

    public function __construct($jsonResponse = false)
    {
        $this->jsonResponse = $jsonResponse;
    }

    public function run(): IView
    {
        $bounds = $this->getMapBounds();

        if (!isset($_SESSION['state']) || $_SESSION['state']['mapId'] !== $this->mapId) {
            $_SESSION['state'] = [
                'mapId' => $this->mapId,
                'area' => $bounds->calculateApproximateArea(),
                'rounds' => []
            ];
        }

        $data = ['bounds' => $bounds->toArray()];

        if ($this->jsonResponse) {
            return new JsonView($data);
        } else {
            return new HtmlView('game', $data);
        }
    }

    private function getMapBounds(): Bounds
    {
        $select = new Select(\Container::$dbConnection, 'maps');
        $select->columns(['bound_south_lat', 'bound_west_lng', 'bound_north_lat', 'bound_east_lng']);
        $select->whereId($this->mapId);

        $map = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

        return $bounds;
    }
}
