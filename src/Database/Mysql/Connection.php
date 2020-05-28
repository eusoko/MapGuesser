<?php namespace MapGuesser\Database\Mysql;

use MapGuesser\Interfaces\Database\IConnection;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Database\IStatement;
use mysqli;

class Connection implements IConnection
{
    private mysqli $connection;

    public function __construct(string $host, string $user, string $password, string $db, int $port = -1, string $socket = null)
    {
        if ($port < 0) {
            $port = ini_get('mysqli.default_port');
        }

        if ($socket === null) {
            $socket = ini_get('mysqli.default_socket');
        }

        $this->connection = new mysqli($host, $user, $password, $db, $port, $socket);

        if ($this->connection->connect_error) {
            throw new \Exception('Connection failed: ' . $this->connection->connect_error);
        }

        if (!$this->connection->set_charset('utf8mb4')) {
            throw new \Exception($this->connection->error);
        }
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    public function startTransaction(): void
    {
        if (!$this->connection->autocommit(false)) {
            throw new \Exception($this->connection->error);
        }
    }

    public function commit(): void
    {
        if (!$this->connection->commit() || !$this->connection->autocommit(true)) {
            throw new \Exception($this->connection->error);
        }
    }

    public function rollback(): void
    {
        if (!$this->connection->rollback() || !$this->connection->autocommit(true)) {
            throw new \Exception($this->connection->error);
        }
    }

    public function query(string $query): ?IResultSet
    {
        if (!($result = $this->connection->query($query))) {
            throw new \Exception($this->connection->error . '. Query: ' . $query);
        }

        if ($result !== true) {
            return new ResultSet($result);
        }

        return null;
    }

    public function multiQuery(string $query): array
    {
        if (!$this->connection->multi_query($query)) {
            throw new \Exception($this->connection->error . '. Query: ' . $query);
        }

        $ret = [];
        do {
            if ($result = $this->connection->store_result()) {
                $ret[] = new ResultSet($result);
            } else {
                $ret[] = null;
            }

            $this->connection->more_results();
        } while ($this->connection->next_result());

        if ($this->connection->error) {
            throw new \Exception($this->connection->error  . '. Query: ' . $query);
        }

        return $ret;
    }

    public function prepare(string $query): IStatement
    {
        if (!($stmt = $this->connection->prepare($query))) {
            throw new \Exception($this->connection->error . '. Query: ' . $query);
        }

        return new Statement($stmt);
    }

    public function lastId(): int
    {
        return $this->connection->insert_id;
    }

    public function getAffectedRows(): int
    {
        return $this->connection->affected_rows;
    }
}
