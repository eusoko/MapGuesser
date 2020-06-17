<?php namespace MapGuesser\Database\Query;

use MapGuesser\Interfaces\Database\IConnection;
use MapGuesser\Database\Utils;

class Modify
{
    private IConnection $connection;

    private string $table;

    private string $idName = 'id';

    private array $attributes = [];

    private ?string $externalId = null;

    private bool $autoIncrement = true;

    public function __construct(IConnection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function setIdName(string $idName): Modify
    {
        $this->idName = $idName;

        return $this;
    }

    public function setExternalId($id): Modify
    {
        $this->externalId = $id;

        return $this;
    }

    public function setAutoIncrement(bool $autoIncrement = true): Modify
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    public function fill(array $attributes): Modify
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function set(string $name, $value): Modify
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function setId($id): Modify
    {
        $this->attributes[$this->idName] = $id;

        return $this;
    }

    public function getId()
    {
        return $this->attributes[$this->idName];
    }

    public function save(): void
    {
        if (isset($this->attributes[$this->idName])) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public function delete(): void
    {
        if (!isset($this->attributes[$this->idName])) {
            throw new \Exception('No primary key specified!');
        }

        $query = 'DELETE FROM ' . Utils::backtick($this->table) . ' WHERE ' . Utils::backtick($this->idName) . '=?';

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$this->idName => $this->attributes[$this->idName]]);
    }

    private function insert(): void
    {
        if ($this->externalId !== null) {
            $this->attributes[$this->idName] = $this->externalId;
        } elseif (!$this->autoIncrement) {
            $this->attributes[$this->idName] = $this->generateKey();
        }

        $set = $this->generateColumnsWithBinding(array_keys($this->attributes));

        $query = 'INSERT INTO ' . Utils::backtick($this->table) . ' SET ' . $set;

        $stmt = $this->connection->prepare($query);
        $stmt->execute($this->attributes);

        if ($this->autoIncrement) {
            $this->attributes[$this->idName] = $this->connection->lastId();
        }
    }

    private function update(): void
    {
        $attributes = $this->attributes;
        unset($attributes[$this->idName]);

        $set = $this->generateColumnsWithBinding(array_keys($attributes));

        $query = 'UPDATE ' . Utils::backtick($this->table) . ' SET ' . $set . ' WHERE ' . Utils::backtick($this->idName) . '=?';

        $stmt = $this->connection->prepare($query);
        $stmt->execute(array_merge($attributes, [$this->idName => $this->attributes[$this->idName]]));
    }

    public static function generateColumnsWithBinding(array $columns): string
    {
        array_walk($columns, function(&$value, $key) {
            $value = Utils::backtick($value) . '=?';
        });

        return implode(',', $columns);
    }

    private function generateKey(): string
    {
        return substr(hash('sha256', serialize($this->attributes) . random_bytes(10) . microtime()), 0, 7);
    }
}
