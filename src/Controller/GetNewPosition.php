<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Position;
use MapGuesser\View\JsonView;
use MapGuesser\View\ViewBase;
use mysqli;

class GetNewPosition implements ControllerInterface
{
    public function run(): ViewBase
    {
        $mysql = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);

        // demo map
        $mapId = 1;

        $stmt = $mysql->prepare('SELECT COUNT(*) AS num FROM places WHERE map_id=?');
        $stmt->bind_param("i", $mapId);
        $stmt->execute();
        $numberOfPlaces = $stmt->get_result()->fetch_assoc()['num'];

        $randomOffset = random_int(0, $numberOfPlaces-1);

        $stmt = $mysql->prepare('SELECT lat, lng FROM places WHERE map_id=? ORDER BY id LIMIT 1 OFFSET ?');
        $stmt->bind_param("ii", $mapId, $randomOffset);
        $stmt->execute();
        $place = $stmt->get_result()->fetch_assoc();

        $position = new Position($place['lat'], $place['lng']);

        $data = ['position' => $position->toArray()];
        return new JsonView($data);
    }
}
