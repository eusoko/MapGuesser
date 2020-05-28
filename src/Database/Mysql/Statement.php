<?php namespace MapGuesser\Database\Mysql;

use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Database\IStatement;
use mysqli_stmt;

class Statement implements IStatement
{
    private mysqli_stmt $stmt;

    public function __construct(mysqli_stmt $stmt)
    {
        $this->stmt = $stmt;
    }

    public function __destruct()
    {
        $this->stmt->close();
    }

    public function execute(array $params = []): ?IResultSet
    {
        if ($params) {
            $ref_params = [''];

            foreach ($params as &$param) {
                $type = gettype($param);

                switch ($type) {
                    case 'integer':
                    case 'double':
                    case 'string':
                        $t = $type[0];
                        break;

                    case 'NULL':
                        $t = 's';
                        break;

                    case 'boolean':
                        $param = (string) (int) $param;
                        $t = 's';
                        break;

                    case 'array':
                        $param = json_encode($param);
                        $t = 's';
                        break;
                }

                if (!isset($t)) {
                    throw new \Exception('Data type ' . $type . ' not supported!');
                }

                $ref_params[] = &$param;
                $ref_params[0] .= $t;
            }

            if (!call_user_func_array([$this->stmt, 'bind_param'], $ref_params)) {
                throw new \Exception($this->stmt->error);
            }
        }

        if (!$this->stmt->execute()) {
            throw new \Exception($this->stmt->error);
        }

        if ($result_set = $this->stmt->get_result()) {
            return new ResultSet($result_set);
        }

        return null;
    }

    public function getAffectedRows(): int
    {
        return $this->stmt->affected_rows;
    }
}
