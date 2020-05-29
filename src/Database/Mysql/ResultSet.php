<?php namespace MapGuesser\Database\Mysql;

use MapGuesser\Interfaces\Database\IResultSet;
use mysqli_result;

class ResultSet implements IResultSet
{
    private mysqli_result $result;

    public function __construct(mysqli_result $result)
    {
        $this->result = $result;
    }

    public function fetch(int $type = IResultSet::FETCH_ASSOC): ?array
    {
        return $this->result->fetch_array($this->convertFetchType($type));
    }

    public function fetchAll(int $type = IResultSet::FETCH_ASSOC): array
    {
        return $this->result->fetch_all($this->convertFetchType($type));
    }

    public function fetchOneColumn(string $valueName, string $keyName = null): array
    {
        $array = [];

        while ($r = $this->fetch(IResultSet::FETCH_ASSOC)) {
            if (isset($keyName)) {
                $array[$r[$keyName]] = $r[$valueName];
            } else {
                $array[] = $r[$valueName];
            }
        }

        return $array;
    }

    private function convertFetchType(int $type): int
    {
        switch ($type) {
            case IResultSet::FETCH_ASSOC:
                $internal_type = MYSQLI_ASSOC;
                break;

            case IResultSet::FETCH_BOTH:
                $internal_type = MYSQLI_BOTH;
                break;

            case IResultSet::FETCH_NUM:
                $internal_type = MYSQLI_NUM;
                break;

            default:
                $internal_type = MYSQLI_BOTH;
                break;
        }

        return $internal_type;
    }
}
