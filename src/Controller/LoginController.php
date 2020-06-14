<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Model\User;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Response\Redirect;

class LoginController
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function getLoginForm()
    {
        $session = $this->request->session();

        if ($session->get('user')) {
            return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
        }

        $data = [];
        return new HtmlContent('login', $data);
    }

    public function login(): IContent
    {
        $session = $this->request->session();

        if ($session->get('user')) {
            $data = ['success' => true];
            return new JsonContent($data);
        }

        $select = new Select(\Container::$dbConnection, 'users');
        $select->columns(User::getFields());
        $select->where('email', '=', $this->request->post('email'));

        $userData = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        if ($userData === null) {
            $data = ['error' => 'user_not_found'];
            return new JsonContent($data);
        }

        $user = new User($userData);

        if (!$user->getActive()) {
            $data = ['error' => 'user_not_active'];
            return new JsonContent($data);
        }

        if (!$user->checkPassword($this->request->post('password'))) {
            $data = ['error' => 'password_not_match'];
            return new JsonContent($data);
        }

        $session->set('user', $user);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function logout(): IRedirect
    {
        $this->request->session()->delete('user');

        return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
    }
}
