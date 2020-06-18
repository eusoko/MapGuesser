<?php namespace MapGuesser\Controller;

use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Mailing\Mail;
use MapGuesser\Model\User;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Response\Redirect;

class SignupController
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function getSignupForm()
    {
        $session = $this->request->session();

        if ($session->get('user')) {
            return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
        }

        $data = [];
        return new HtmlContent('signup/signup', $data);
    }

    public function signup(): IContent
    {
        $session = $this->request->session();

        if ($session->get('user')) {
            //TODO: return with some error
            $data = ['success' => true];
            return new JsonContent($data);
        }

        if (filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL) === false) {
            $data = ['error' => 'email_not_valid'];
            return new JsonContent($data);
        }

        $select = new Select(\Container::$dbConnection, 'users');
        $select->columns(User::getFields());
        $select->where('email', '=', $this->request->post('email'));

        $userData = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        if ($userData !== null) {
            $user = new User($userData);

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

        $user = new User([
            'email' => $this->request->post('email'),
        ]);

        $user->setPlainPassword($this->request->post('password'));

        \Container::$dbConnection->startTransaction();

        $modify = new Modify(\Container::$dbConnection, 'users');
        $modify->fill($user->toArray());
        $modify->save();
        $userId = $modify->getId();

        $token = hash('sha256', serialize($user) . random_bytes(10) . microtime());

        $modify = new Modify(\Container::$dbConnection, 'user_confirmations');
        $modify->set('user_id', $userId);
        $modify->set('token', $token);
        $modify->save();

        \Container::$dbConnection->commit();

        $this->sendConfirmationEmail($user->getEmail(), $token);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function activate()
    {
        $session = $this->request->session();

        if ($session->get('user')) {
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

        $modify = new Modify(\Container::$dbConnection, 'users');
        $modify->setId($confirmation['user_id']);
        $modify->set('active', true);
        $modify->save();

        \Container::$dbConnection->commit();

        $select = new Select(\Container::$dbConnection, 'users');
        $select->columns(User::getFields());
        $select->whereId($confirmation['user_id']);

        $userData = $select->execute()->fetch(IResultSet::FETCH_ASSOC);
        $user = new User($userData);

        $session->set('user', $user);

        return new Redirect([\Container::$routeCollection->getRoute('index'), []], IRedirect::TEMPORARY);
    }

    public function cancel()
    {
        $session = $this->request->session();

        if ($session->get('user')) {
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

        $modify = new Modify(\Container::$dbConnection, 'users');
        $modify->setId($confirmation['user_id']);
        $modify->delete();

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
