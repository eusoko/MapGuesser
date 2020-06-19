<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Mailing\Mail;
use MapGuesser\PersistentData\PersistentDataManager;
use MapGuesser\PersistentData\Model\User;
use MapGuesser\Repository\UserRepository;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Response\Redirect;

class SignupController
{
    private IRequest $request;

    private PersistentDataManager $pdm;

    private UserRepository $userRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->pdm = new PersistentDataManager();
        $this->userRepository = new UserRepository();
    }

    public function getSignupForm()
    {
        if ($this->request->user() !== null) {
            return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
        }

        $data = [];
        return new HtmlContent('signup/signup', $data);
    }

    public function signup(): IContent
    {
        if ($this->request->user() !== null) {
            //TODO: return with some error
            $data = ['success' => true];
            return new JsonContent($data);
        }

        if (filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL) === false) {
            $data = ['error' => 'email_not_valid'];
            return new JsonContent($data);
        }

        $user = $this->userRepository->getByEmail($this->request->post('email'));

        if ($user !== null) {
            if ($user->getActive()) {
                $data = ['error' => 'user_found'];
            } else {
                $data = ['error' => 'not_active_user_found'];
            }
            return new JsonContent($data);
        }

        if (strlen($this->request->post('password')) < 6) {
            $data = ['error' => 'passwords_too_short'];
            return new JsonContent($data);
        }

        if ($this->request->post('password') !== $this->request->post('password_confirm')) {
            $data = ['error' => 'passwords_not_match'];
            return new JsonContent($data);
        }

        $user = new User();
        $user->setEmail($this->request->post('email'));
        $user->setPlainPassword($this->request->post('password'));

        \Container::$dbConnection->startTransaction();

        $this->pdm->saveToDb($user);

        $token = hash('sha256', serialize($user) . random_bytes(10) . microtime());

        $modify = new Modify(\Container::$dbConnection, 'user_confirmations');
        $modify->set('user_id', $user->getId());
        $modify->set('token', $token);
        $modify->save();

        \Container::$dbConnection->commit();

        $this->sendConfirmationEmail($user->getEmail(), $token);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function activate()
    {
        if ($this->request->user() !== null) {
            return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
        }

        $select = new Select(\Container::$dbConnection, 'user_confirmations');
        $select->columns(['id', 'user_id']);
        $select->where('token', '=', $this->request->query('token'));

        $confirmation = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        if ($confirmation === null) {
            $data = [];
            return new HtmlContent('signup/activate', $data);
        }

        \Container::$dbConnection->startTransaction();

        $modify = new Modify(\Container::$dbConnection, 'user_confirmations');
        $modify->setId($confirmation['id']);
        $modify->delete();

        $user = $this->userRepository->getById($confirmation['user_id']);
        $user->setActive(true);

        $this->pdm->saveToDb($user);

        \Container::$dbConnection->commit();

        $this->request->setUser($user);

        return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
    }

    public function cancel()
    {
        if ($this->request->user() !== null) {
            return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
        }

        $select = new Select(\Container::$dbConnection, 'user_confirmations');
        $select->columns(['id', 'user_id']);
        $select->where('token', '=', $this->request->query('token'));

        $confirmation = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        if ($confirmation === null) {
            $data = ['success' => false];
            return new HtmlContent('signup/cancel', $data);
        }

        \Container::$dbConnection->startTransaction();

        $modify = new Modify(\Container::$dbConnection, 'user_confirmations');
        $modify->setId($confirmation['id']);
        $modify->delete();

        $user = $this->userRepository->getById($confirmation['user_id']);

        $this->pdm->deleteFromDb($user);

        \Container::$dbConnection->commit();

        $data = ['success' => true];
        return new HtmlContent('signup/cancel', $data);
    }

    private function sendConfirmationEmail($email, $token): void
    {
        $mail = new Mail();
        $mail->addRecipient($email);
        $mail->setSubject('Welcome to MapGuesser - Activate your account');
        $mail->setBodyFromTemplate('signup', [
            'EMAIL' => $email,
            'ACTIVATE_LINK' => $this->request->getBase() . '/signup/activate/' . $token,
            'CANCEL_LINK' => $this->request->getBase() . '/signup/cancel/' . $token,
        ]);
        $mail->send();
    }
}
