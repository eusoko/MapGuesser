<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Authorization\ISecured;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\PersistentData\PersistentDataManager;
use MapGuesser\PersistentData\Model\User;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;

class UserController implements ISecured
{
    private IRequest $request;

    private PersistentDataManager $pdm;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->pdm = new PersistentDataManager();
    }

    public function authorize(): bool
    {
        $user = $this->request->user();

        return $user !== null;
    }

    public function getProfile(): IContent
    {
        /**
         * @var User $user
         */
        $user = $this->request->user();

        $data = ['user' => $user->toArray()];
        return new HtmlContent('profile', $data);
    }

    public function saveProfile(): IContent
    {
        /**
         * @var User $user
         */
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

        $this->pdm->saveToDb($user);

        $this->request->session()->set('user', $user);

        $data = ['success' => true];
        return new JsonContent($data);
    }
}
