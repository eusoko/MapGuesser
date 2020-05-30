<?php

namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Database\RawExpression;
use MapGuesser\Interfaces\Controller\IController;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\View\IView;
use MapGuesser\Util\Geo\Bounds;
use MapGuesser\View\HtmlView;

class MapsController implements IController
{
    public function run(): IView
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
        $select->orderBy('name');

        $result = $select->execute();

        $maps = [];
        while ($map = $result->fetch(IResultSet::FETCH_ASSOC)) {
            $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

            $map['area'] = $this->formatMapArea($bounds->calculateApproximateArea());

            $maps[] = $map;
        }

        $data = ['maps' => $maps];
        return new HtmlView('maps', $data);
    }

    private function formatMapArea(float $area): string
    {
        //TODO: this should be formatted more properly

        if ($area < 100000.0) {
            return round($area, 0) . ' m^2';
        } elseif ($area < 100000000.0) {
            return round($area / 1000000.0, 0) . ' km^2';
        } elseif ($area < 10000000000.0) {
            return round($area / 1000000.0, -2) . ' km^2';
        } else {
            return round($area / 1000000.0, -4) . ' km^2';
        }
    }
}
