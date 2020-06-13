<?php namespace MapGuesser\Database\Query;

use MapGuesser\Interfaces\Database\IConnection;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Database\Utils;

class Modify
{
    private IConnection $connection;

    private string $table;

    private string $idName = 'id';

    private array $attributes = [];

    private array $original = [];

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
        $diff = $this->generateDiff();

        if (count($diff) === 0) {
            return;
        }

        $set = $this->generateColumnsWithBinding(array_keys($diff));

        $query = 'UPDATE ' . Utils::backtick($this->table) . ' SET ' . $set . ' WHERE ' . Utils::backtick($this->idName) . '=?';

        $stmt = $this->connection->prepare($query);
        $stmt->execute(array_merge($diff, [$this->idName => $this->attributes[$this->idName]]));
    }

    private function readFromDB(array $columns): void
    {
        $select = (new Select($this->connection, $this->table))
            ->setIdName($this->idName)
            ->whereId($this->attributes[$this->idName])
            ->columns($columns);

        $this->original = $select->execute()->fetch(IResultSet::FETCH_ASSOC);
    }

    private function generateDiff(): array
    {
        $this->readFromDB(array_keys($this->attributes));

        $diff = [];

        foreach ($this->attributes as $name => $value) {
            $original = $this->original[$name];

            if ($original != $value) {
                $diff[$name] = $value;
            }
        }

        return $diff;
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
