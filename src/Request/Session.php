<?php namespace MapGuesser\Request;

use MapGuesser\Interfaces\Request\ISession;

class Session implements ISession
{
    private array $data;

    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function delete($key): void
    {
        unset($this->data[$key]);
    }
}
