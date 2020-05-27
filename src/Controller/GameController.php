<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Controller\IController;
use MapGuesser\Util\Geo\Bounds;
use MapGuesser\View\HtmlView;
use MapGuesser\View\JsonView;
use MapGuesser\Interfaces\View\IView;
use mysqli;

class GameController implements IController
{
    private mysqli $mysql;

    private bool $jsonResponse;

    // demo map
    private int $mapId = 1;

    public function __construct($jsonResponse = false)
    {
        $this->mysql = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

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
        $stmt = $this->mysql->prepare('SELECT bound_south_lat, bound_west_lng, bound_north_lat, bound_east_lng FROM maps WHERE id=?');
        $stmt->bind_param("i", $this->mapId);
        $stmt->execute();
        $map = $stmt->get_result()->fetch_assoc();

        $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

        return $bounds;
    }
}
