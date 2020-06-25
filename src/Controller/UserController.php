<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Authorization\ISecured;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\PersistentData\PersistentDataManager;
use MapGuesser\PersistentData\Model\User;
use MapGuesser\PersistentData\Model\UserConfirmation;
use MapGuesser\Repository\UserConfirmationRepository;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;

class UserController implements ISecured
{
    private IRequest $request;

    private PersistentDataManager $pdm;

    private UserConfirmationRepository $userConfirmationRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->pdm = new PersistentDataManager();
        $this->userConfirmationRepository = new UserConfirmationRepository();
    }

    public function authorize(): bool
    {
        $user = $this->request->user();

        return $user !== null;
    }

    public function getAccount(): IContent
    {
        /**
         * @var User $user
         */
        $user = $this->request->user();

        $data = ['user' => $user->toArray()];
        return new HtmlContent('account/account', $data);
    }

    public function getDeleteAccount(): IContent
    {
        /**
         * @var User $user
         */
        $user = $this->request->user();

        $data = ['user' => $user->toArray()];
        return new HtmlContent('account/delete', $data);
    }

    public function saveAccount(): IContent
    {
        /**
         * @var User $user
         */
        $user = $this->request->user();

        if (!$user->checkPassword($this->request->post('password'))) {
            $data = ['error' => ['errorText' => 'The given current password is wrong.']];
            return new JsonContent($data);
        }

        if (strlen($this->request->post('password_new')) > 0) {
            if (strlen($this->request->post('password_new')) < 6) {
                $data = ['error' => ['errorText' => 'The given new password is too short. Please choose a password that is at least 6 characters long!']];
                return new JsonContent($data);
            }

            if ($this->request->post('password_new') !== $this->request->post('password_new_confirm')) {
                $data = ['error' => ['errorText' => 'The given new passwords do not match.']];
                return new JsonContent($data);
            }

            $user->setPlainPassword($this->request->post('password_new'));
        }

        $this->pdm->saveToDb($user);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function deleteAccount(): IContent
    {
        /**
         * @var User $user
         */
        $user = $this->request->user();

        if (!$user->checkPassword($this->request->post('password'))) {
            $data = ['error' => ['errorText' => 'The given current password is wrong.']];
            return new JsonContent($data);
        }

        \Container::$dbConnection->startTransaction();

        foreach ($this->userConfirmationRepository->getByUser($user) as $userConfirmation) {
            $this->pdm->deleteFromDb($userConfirmation);
        }

        $this->pdm->deleteFromDb($user);

        \Container::$dbConnection->commit();

        $data = ['success' => true];
        return new JsonContent($data);
    }
}
