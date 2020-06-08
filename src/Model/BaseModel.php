<?php namespace MapGuesser\Model;

abstract class BaseModel
{
    protected static array $fields;

    protected $id = null;

    public static function getFields(): array
    {
        return array_merge(['id'], static::$fields);
    }

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    function toArray(): array
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
}
