<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Repository\MapRepository;

class GameController
{
    private IRequest $request;

    private MapRepository $mapRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->mapRepository = new MapRepository();
    }

    public function getGame(): IContent
    {
        $mapId = (int) $this->request->query('mapId');
        $data = $this->prepareGame($mapId);
        return new HtmlContent('game', $data);
    }

    public function getGameJson(): IContent
    {
        $mapId = (int) $this->request->query('mapId');
        $data = $this->prepareGame($mapId);
        return new JsonContent($data);
    }

    private function prepareGame(int $mapId)
    {
        $map = $this->mapRepository->getById($mapId);

        $session = $this->request->session();

        if (!($state = $session->get('state')) || $state['mapId'] !== $mapId) {
            $session->set('state', [
                'mapId' => $mapId,
                'area' => $map->getArea(),
                'rounds' => []
            ]);
        }

        return ['mapId' => $mapId, 'mapName' => $map->getName(), 'bounds' => $map->getBounds()->toArray()];
    }
}
