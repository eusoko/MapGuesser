<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Modify;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;

class UserController
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function getProfile(): IContent
    {
        $user = $this->request->user();

        $data = ['user' => $user->toArray()];
        return new HtmlContent('profile', $data);
    }

    public function saveProfile(): IContent
    {
        $user = $this->request->user();

        if (!$user->checkPassword($this->request->post('password'))) {
            $data = ['error' => 'password_not_match'];
            return new JsonContent($data);
        }

        if (strlen($this->request->post('password_new')) > 0) {
            if (strlen($this->request->post('password_new')) < 6) {
                $data = ['error' => 'passwords_too_short'];
                return new JsonContent($data);
            }

            if ($this->request->post('password_new') !== $this->request->post('password_new_confirm')) {
                $data = ['error' => 'passwords_not_match'];
                return new JsonContent($data);
            }

            $user->setPlainPassword($this->request->post('password_new'));
        }

        $modify = new Modify(\Container::$dbConnection, 'users');
        $modify->fill($user->toArray());
        $modify->save();

        $data = ['success' => true];
        return new JsonContent($data);
    }
}