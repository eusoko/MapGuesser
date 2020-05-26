<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Position;
use MapGuesser\View\JsonView;
use MapGuesser\View\ViewBase;
use mysqli;
use RestClient\Client;

class PositionController implements ControllerInterface
{
    const NUMBER_OF_ROUNDS = 5;
    const MAX_SCORE = 1000;

    private mysqli $mysql;

    // demo map
    private int $mapId = 1;

    public function __construct()
    {
        $this->mysql = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
    }

    public function run(): ViewBase
    {
        if (!isset($_SESSION['state']) || $_SESSION['state']['mapId'] !== $this->mapId) {
            $data = ['error' => 'No valid session found!'];
            return new JsonView($data);
        }

        if (count($_SESSION['state']['rounds']) === 0) {
            $newPosition = $this->getNewPosition();
            $_SESSION['state']['rounds'][] = $newPosition;

            $data = ['panoId' => $newPosition['panoId']];
        } elseif (isset($_POST['guess'])) {
            $last = &$_SESSION['state']['rounds'][count($_SESSION['state']['rounds']) - 1];

            $position = $last['position'];
            $guessPosition = new Position((float) $_POST['lat'], (float) $_POST['lng']);

            $last['guessPosition'] = $guessPosition;

            $distance = $this->calculateDistance($position, $guessPosition);
            $score = $this->calculateScore($distance, $_SESSION['state']['area']);

            $last['distance'] = $distance;
            $last['score'] = $score;

            if (count($_SESSION['state']['rounds']) < static::NUMBER_OF_ROUNDS) {
                $exclude = [];

                foreach ($_SESSION['state']['rounds'] as $round) {
                    $exclude = array_merge($exclude, $round['placesWithoutPano'], [$round['placeId']]);
                }

                $newPosition = $this->getNewPosition($exclude);
                $_SESSION['state']['rounds'][] = $newPosition;

                $panoId = $newPosition['panoId'];
            } else {
                $_SESSION['state']['rounds'] = [];

                $panoId = null;
            }

            $data = [
                'result' => [
                    'position' => $position->toArray(),
                    'distance' => $distance,
                    'score' => $score
                ],
                'panoId' => $panoId
            ];
        } else {
            $rounds = count($_SESSION['state']['rounds']);
            $last = $_SESSION['state']['rounds'][$rounds - 1];

            $history = [];
            for ($i = 0; $i < $rounds - 1; ++$i) {
                $round = $_SESSION['state']['rounds'][$i];
                $history[] = [
                    'position' => $round['position']->toArray(),
                    'guessPosition' => $round['guessPosition']->toArray(),
                    'distance' => $round['distance'],
                    'score' => $round['score']
                ];
            }

            $data = [
                'history' => $history,
                'panoId' => $last['panoId']
            ];
        }

        return new JsonView($data);
    }

    private function getNewPosition(array $exclude = []): array
    {
        $placesWithoutPano = [];

        do {
            $place = $this->selectNewPlace($exclude);
            $position = new Position($place['lat'], $place['lng']);
            $panoId = $this->getPanorama($position);

            if ($panoId === null) {
                $placesWithoutPano[] = $place['id'];
            }
        } while ($panoId === null);

        return [
            'placesWithoutPano' => $placesWithoutPano,
            'placeId' => $place['id'],
            'position' => $position,
            'panoId' => $panoId
        ];
    }

    private function selectNewPlace(array $exclude): array
    {
        $condition = '';
        $params = ['i', &$this->mapId];
        if (($numExcluded = count($exclude)) > 0) {
            $condition .= ' AND id NOT IN (' . implode(',', array_fill(0, $numExcluded, '?')) . ')';
            $params[0] .= str_repeat('i', $numExcluded);
            foreach ($exclude as &$placeId) {
                $params[] = &$placeId;
            }
        }

        $stmt = $this->mysql->prepare('SELECT COUNT(*) AS num FROM places WHERE map_id=? ' . $condition . '');
        call_user_func_array([$stmt, 'bind_param'], $params);
        $stmt->execute();
        $numberOfPlaces = $stmt->get_result()->fetch_assoc()['num'];
        $randomOffset = random_int(0, $numberOfPlaces - 1);

        $params[0] .= 'i';
        $params[] = &$randomOffset;

        $stmt = $this->mysql->prepare('SELECT id, lat, lng FROM places WHERE map_id=? ' . $condition . ' ORDER BY id LIMIT 1 OFFSET ?');
        call_user_func_array([$stmt, 'bind_param'], $params);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    private function getPanorama(Position $position): ?string
    {
        $query = [
            'key' => $_ENV['GOOGLE_MAPS_SERVER_API_KEY'],
            'location' => $position->getLat() . ',' . $position->getLng(),
            'source' => 'outdoor'
        ];

        $client = new Client('https://maps.googleapis.com/maps/api/streetview');
        $request = $client->newRequest('metadata?' . http_build_query($query));
        $response = $request->getResponse();

        $panoData = json_decode($response->getParsedResponse(), true);

        if ($panoData['status'] !== 'OK') {
            return null;
        }

        return $panoData['pano_id'];
    }

    private function calculateDistance(Position $realPosition, Position $guessPosition): float
    {
        return $realPosition->calculateDistanceTo($guessPosition);
    }

    private function calculateScore(float $distance, float $area)
    {
        $goodness = 1.0 - ($distance / sqrt($area));

        return round(pow(static::MAX_SCORE, $goodness));
    }
}
