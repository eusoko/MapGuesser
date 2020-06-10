<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Database\RawExpression;
use MapGuesser\Interfaces\Authentication\IUser;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Util\Geo\Bounds;
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
            new RawExpression('COUNT(places.id) AS num_places')
        ]);
        $select->leftJoin('places', ['places', 'map_id'], '=', ['maps', 'id']);
        $select->groupBy(['maps', 'id']);
        $select->orderBy('name');

        $result = $select->execute();

        $maps = [];
        while ($map = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

            $map['area'] = $this->formatMapAreaForHuman($bounds->calculateApproximateArea());

            $maps[] = $map;
        }

        $user = $this->request->user();
        $data = ['maps' => $maps, 'isAdmin' => $user !== null && $user->hasPermission(IUser::PERMISSION_ADMIN)];
        return new HtmlContent('maps', $data);
    }

    private function formatMapAreaForHuman(float $area): array
    {
        if ($area < 100000.0) {
            $digits = 0;
            $rounded = round($area, 0);
            $unit = 'm';
        } elseif ($area < 100000000.0) {
            $digits = 0;
            $rounded = round($area / 1000000.0, 0);
            $unit = 'km';
        } elseif ($area < 10000000000.0) {
            $digits = 0;
            $rounded = round($area / 1000000.0, -2);
            $unit = 'km';
        } else {
            $digits = 0;
            $rounded = round($area / 1000000.0, -4);
            $unit = 'km';
        }

        return [number_format($rounded, $digits, '.', ' '), $unit];
    }
}
