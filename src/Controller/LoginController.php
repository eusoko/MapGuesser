<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Repository\UserRepository;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Response\Redirect;

class LoginController
{
    private IRequest $request;

    private UserRepository $userRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->userRepository = new UserRepository();
    }

    public function getLoginForm()
    {
        if ($this->request->user() !== null) {
            return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
        }

        $data = [];
        return new HtmlContent('login', $data);
    }

    public function login(): IContent
    {
        if ($this->request->user() !== null) {
            $data = ['success' => true];
            return new JsonContent($data);
        }

        $user = $this->userRepository->getByEmail($this->request->post('email'));

        if ($user === null) {
            $data = ['error' => 'user_not_found'];
            return new JsonContent($data);
        }

        if (!$user->getActive()) {
            $data = ['error' => 'user_not_active'];
            return new JsonContent($data);
        }

        if (!$user->checkPassword($this->request->post('password'))) {
            $data = ['error' => 'password_not_match'];
            return new JsonContent($data);
        }

        $this->request->setUser($user);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function logout(): IRedirect
    {
        $this->request->setUser(null);

        return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
    }
}
