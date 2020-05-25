<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Bounds;
use MapGuesser\View\HtmlView;
use MapGuesser\View\ViewBase;
use mysqli;

class GameController implements ControllerInterface
{
    private mysqli $mysql;

    // demo map
    private int $mapId = 1;

    public function __construct()
    {
        $this->mysql = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
    }

    public function run(): ViewBase
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
        return new HtmlView('game', $data);
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
