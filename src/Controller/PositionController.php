<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Http\Request;
use MapGuesser\Interfaces\Controller\IController;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Util\Geo\Position;
use MapGuesser\View\JsonView;
use MapGuesser\Interfaces\View\IView;

class PositionController implements IController
{
    const NUMBER_OF_ROUNDS = 5;
    const MAX_SCORE = 1000;

    // demo map
    private int $mapId = 1;

    public function run(): IView
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
        $select = new Select(\Container::$dbConnection, 'places');
        $select->columns(['id', 'lat', 'lng']);
        $select->where('id', 'NOT IN', $exclude);
        $select->where('map_id', '=', $this->mapId);

        $numberOfPlaces = $select->count();
        $randomOffset = random_int(0, $numberOfPlaces - 1);

        $select->orderBy('id');
        $select->limit(1, $randomOffset);

        $place = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        return $place;
    }

    private function getPanorama(Position $position): ?string
    {
        $request = new Request('https://maps.googleapis.com/maps/api/streetview/metadata', Request::HTTP_GET);
        $request->setQuery([
            'key' => $_ENV['GOOGLE_MAPS_SERVER_API_KEY'],
            'location' => $position->getLat() . ',' . $position->getLng(),
            'source' => 'outdoor'
        ]);

        $response = $request->send();

        $panoData = json_decode($response->getBody(), true);

        if ($panoData['status'] !== 'OK') {
            return null;
        }

        return $panoData['pano_id'];
    }

    private function calculateDistance(Position $realPosition, Position $guessPosition): float
    {
        return $realPosition->calculateDistanceTo($guessPosition);
    }

    private function calculateScore(float $distance, float $area): int
    {
        $goodness = 1.0 - ($distance / sqrt($area));

        return (int) round(pow(static::MAX_SCORE, $goodness));
    }
}
