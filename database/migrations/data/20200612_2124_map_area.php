<?php

use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Util\Geo\Bounds;

$select = new Select(\Container::$dbConnection, 'maps');
$select->columns(['id', 'bound_south_lat', 'bound_west_lng', 'bound_north_lat', 'bound_east_lng']);

$result = $select->execute();

\Container::$dbConnection->startTransaction();

while ($map = $result->fetch(IResultSet::FETCH_ASSOC)) {
    $bounds = Bounds::createDirectly($map['bound_south_lat'], $map['bound_west_lng'], $map['bound_north_lat'], $map['bound_east_lng']);

    $modify = new Modify(\Container::$dbConnection, 'maps');
    $modify->setId($map['id']);
    $modify->set('area', $bounds->calculateApproximateArea());
    $modify->save();
}

\Container::$dbConnection->commit();
