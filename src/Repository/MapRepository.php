<?php namespace MapGuesser\Repository;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;

class MapRepository
{
    public function getById(int $mapId)
    {
        $select = new Select(\Container::$dbConnection, 'maps');
        $select->columns(['id', 'name', 'description', 'bound_south_lat', 'bound_west_lng', 'bound_north_lat', 'bound_east_lng']);
        $select->whereId($mapId);

        return $select->execute()->fetch(IResultSet::FETCH_ASSOC);
    }
}
