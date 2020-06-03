<?php namespace MapGuesser\Controller;

use MapGuesser\Util\Geo\Position;
use MapGuesser\Response\JsonContent;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Repository\PlaceRepository;

class PositionController
{
    const NUMBER_OF_ROUNDS = 5;
    const MAX_SCORE = 1000;

    private PlaceRepository $placeRepository;

    public function __construct()
    {
        $this->placeRepository = new PlaceRepository();
    }

    public function getPosition(array $parameters): IContent
    {
        $mapId = (int) $parameters['mapId'];

        if (!isset($_SESSION['state']) || $_SESSION['state']['mapId'] !== $mapId) {
            $data = ['error' => 'No valid session found!'];
            return new JsonContent($data);
        }

        if (count($_SESSION['state']['rounds']) === 0) {
            $newPosition = $this->placeRepository->getForMapWithValidPano($mapId);
            $_SESSION['state']['rounds'][] = $newPosition;

            $data = ['panoId' => $newPosition['panoId']];
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

        return new JsonContent($data);
    }

    public function evaluateGuess(array $parameters): IContent
    {
        $mapId = (int) $parameters['mapId'];

        if (!isset($_SESSION['state']) || $_SESSION['state']['mapId'] !== $mapId) {
            $data = ['error' => 'No valid session found!'];
            return new JsonContent($data);
        }

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

            $newPosition = $this->placeRepository->getForMapWithValidPano($mapId, $exclude);
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
        return new JsonContent($data);
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
