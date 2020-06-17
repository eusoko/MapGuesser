<?php namespace MapGuesser\PersistentData\Model;

abstract class Model
{
    protected static string $table;

    protected static array $fields;

    protected static array $relations = [];

    protected $id = null;

    private array $snapshot = [];

    public static function getTable(): string
    {
        return static::$table;
    }

    public static function getFields(): array
    {
        return array_merge(['id'], static::$fields);
    }

    public static function getRelations(): array
    {
        return static::$relations;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function toArray(): array
    {
        $array = [];

        foreach (self::getFields() as $key) {
            $method = 'get' . str_replace('_', '', ucwords($key, '_'));

            if (method_exists($this, $method)) {
                $array[$key] = $this->$method();
            }
        }

        return $array;
    }

    public function saveSnapshot(): void
    {
        $this->snapshot = $this->toArray();
    }

    public function resetSnapshot(): void
    {
        $this->snapshot = [];
    }

    public function getSnapshot(): array
    {
        return $this->snapshot;
    }
}
