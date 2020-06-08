<?php namespace MapGuesser\Request;

use MapGuesser\Interfaces\Authentication\IUser;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Request\ISession;
use MapGuesser\Model\User;

class Request implements IRequest
{
    private array $get;

    private array $routeParams;

    private array $post;

    private Session $session;

    public function __construct(array &$get, array &$routeParams, array &$post, array &$session)
    {
        $this->get = &$get;
        $this->routeParams = &$routeParams;
        $this->post = &$post;
        $this->session = new Session($session);
    }

    public function query($key)
    {
        if (isset($this->get[$key])) {
            return $this->get[$key];
        }

        if (isset($this->routeParams[$key])) {
            return $this->routeParams[$key];
        }

        return null;
    }

    public function post($key)
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        return null;
    }

    public function session(): ISession
    {
        return $this->session;
    }

    public function user(): ?IUser
    {
        return $this->session->get('user');
    }
}
