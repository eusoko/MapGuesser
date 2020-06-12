<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Database\RawExpression;
use MapGuesser\Interfaces\Authentication\IUser;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Response\HtmlContent;

class MapsController
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function getMaps(): IContent
    {
        $select = new Select(\Container::$dbConnection, 'maps');
        $select->columns([
            ['maps', 'id'],
            ['maps', 'name'],
            ['maps', 'description'],
            ['maps', 'bound_south_lat'],
            ['maps', 'bound_west_lng'],
            ['maps', 'bound_north_lat'],
            ['maps', 'bound_east_lng'],
            ['maps', 'area'],
            new RawExpression('COUNT(places.id) AS num_places')
        ]);
        $select->leftJoin('places', ['places', 'map_id'], '=', ['maps', 'id']);
        $select->groupBy(['maps', 'id']);
        $select->orderBy('name');

        $result = $select->execute();

        $maps = [];
        while ($map = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $map['area'] = $this->formatMapAreaForHuman($map['area']);

            $maps[] = $map;
        }

        $user = $this->request->user();
        $data = ['maps' => $maps, 'isAdmin' => $user !== null && $user->hasPermission(IUser::PERMISSION_ADMIN)];
        return new HtmlContent('maps', $data);
    }

    private function formatMapAreaForHuman(float $area): array
    {
        if ($area < 0.01) {
            $digits = 0;
            $rounded = round($area * 1000000.0, -2);
            $unit = 'm';
        } elseif ($area < 0.1) {
            $digits = 0;
            $rounded = round($area * 1000000.0, -3);
            $unit = 'm';
        } elseif ($area < 1.0) {
            $digits = 2;
            $rounded = round($area, 2);
            $unit = 'km';
        } elseif ($area < 100.0) {
            $digits = 0;
            $rounded = round($area, 0);
            $unit = 'km';
        } elseif ($area < 10000.0) {
            $digits = 0;
            $rounded = round($area, -2);
            $unit = 'km';
        } else {
            $digits = 0;
            $rounded = round($area, -4);
            $unit = 'km';
        }

        return [number_format($rounded, $digits, '.', ' '), $unit];
    }
}
