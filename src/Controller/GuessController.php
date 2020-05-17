<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Bounds;
use MapGuesser\Util\Geo\Position;
use mysqli;

class GuessController extends BaseController
{
    protected string $view = 'guess';

    protected function operate() : void
    {
        $mysql = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

        // demo map
        $mapId = 1;

        // using RAND() for the time being, could be changed in the future
        $stmt = $mysql->prepare('SELECT lat, lng FROM places WHERE map_id=? ORDER BY RAND() LIMIT 1');
        $stmt->bind_param("i", $mapId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $realPosition = new Position($row['lat'], $row['lng']);

        // demo bounds
        $bounds = new Bounds($realPosition);
        $bounds->extend(new Position(48.07683,7.35758));
        $bounds->extend(new Position(47.57496, 19.08077));

        $this->variables = compact('realPosition', 'bounds');
    }
}
