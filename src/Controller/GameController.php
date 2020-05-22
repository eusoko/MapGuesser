<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Util\Geo\Position;
use MapGuesser\View\HtmlView;
use MapGuesser\View\ViewBase;
use mysqli;

class GameController implements ControllerInterface
{
    public function run(): ViewBase
    {
        $mysql = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

        // demo map
        $mapId = 1;

        $stmt = $mysql->prepare('SELECT bound_south_lat, bound_west_lng, bound_north_lat, bound_east_lng FROM maps WHERE id=?');
        $stmt->bind_param("i", $mapId);
        $stmt->execute();
        $map = $stmt->get_result()->fetch_assoc();

        $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

        $data = compact('bounds');
        return new HtmlView('game', $data);
    }
}
