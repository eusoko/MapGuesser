<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Util\Geo\Position;
use MapGuesser\Response\JsonContent;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Repository\PlaceRepository;

class PositionController
{
    const NUMBER_OF_ROUNDS = 5;
    const MAX_SCORE = 1000;

    private IRequest $request;

    private PlaceRepository $placeRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->placeRepository = new PlaceRepository();
    }

    public function getPosition(): IContent
    {
        $mapId = (int) $this->request->query('mapId');

        $session = $this->request->session();

        if (!($state = $session->get('state')) || $state['mapId'] !== $mapId) {
            $data = ['error' => 'no_session_found'];
            return new JsonContent($data);
        }

        if (count($state['rounds']) === 0) {
            $newPosition = $this->placeRepository->getForMapWithValidPano($mapId);
            $state['rounds'][] = $newPosition;
            $session->set('state', $state);

            $data = ['panoId' => $newPosition['panoId']];
        } else {
            $rounds = count($state['rounds']);
            $last = $state['rounds'][$rounds - 1];

            $history = [];
            for ($i = 0; $i < $rounds - 1; ++$i) {
                $round = $state['rounds'][$i];
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

    public function evaluateGuess(): IContent
    {
        $mapId = (int) $this->request->query('mapId');

        $session = $this->request->session();

        if (!($state = $session->get('state')) || $state['mapId'] !== $mapId) {
            $data = ['error' => 'no_session_found'];
            return new JsonContent($data);
        }

        $last = $state['rounds'][count($state['rounds']) - 1];

        $position = $last['position'];
        $guessPosition = new Position((float) $this->request->post('lat'), (float) $this->request->post('lng'));

        $distance = $this->calculateDistance($position, $guessPosition);
        $score = $this->calculateScore($distance, $state['area']);

        $last['guessPosition'] = $guessPosition;
        $last['distance'] = $distance;
        $last['score'] = $score;
        $state['rounds'][count($state['rounds']) - 1] = $last;

        if (count($state['rounds']) < static::NUMBER_OF_ROUNDS) {
            $exclude = [];

            foreach ($state['rounds'] as $round) {
                $exclude = array_merge($exclude, $round['placesWithoutPano'], [$round['placeId']]);
            }

            $newPosition = $this->placeRepository->getForMapWithValidPano($mapId, $exclude);
            $state['rounds'][] = $newPosition;
            $session->set('state', $state);

            $panoId = $newPosition['panoId'];
        } else {
            $state['rounds'] = [];
            $session->set('state', $state);

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
