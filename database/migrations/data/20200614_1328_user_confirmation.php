<?php

use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;

$select = new Select(\Container::$dbConnection, 'users');
$select->columns(['id']);

$result = $select->execute();

while ($map = $result->fetch(IResultSet::FETCH_ASSOC)) {
    $modify = new Modify(\Container::$dbConnection, 'users');
    $modify->setId($map['id']);
    $modify->set('active', true);
    $modify->save();
}
