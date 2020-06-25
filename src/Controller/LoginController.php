<?php namespace MapGuesser\Controller;

use MapGuesser\Interfaces\Request\IRequest;
use MapGuesser\Interfaces\Response\IContent;
use MapGuesser\Interfaces\Response\IRedirect;
use MapGuesser\Mailing\Mail;
use MapGuesser\OAuth\GoogleOAuth;
use MapGuesser\PersistentData\Model\User;
use MapGuesser\PersistentData\Model\UserConfirmation;
use MapGuesser\PersistentData\PersistentDataManager;
use MapGuesser\Repository\UserConfirmationRepository;
use MapGuesser\Repository\UserRepository;
use MapGuesser\Response\HtmlContent;
use MapGuesser\Response\JsonContent;
use MapGuesser\Response\Redirect;
use MapGuesser\Util\JwtParser;

class LoginController
{
    private IRequest $request;

    private PersistentDataManager $pdm;

    private UserRepository $userRepository;

    private UserConfirmationRepository $userConfirmationRepository;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
        $this->pdm = new PersistentDataManager();
        $this->userRepository = new UserRepository();
        $this->userConfirmationRepository = new UserConfirmationRepository();
    }

    public function getLoginForm()
    {
        if ($this->request->user() !== null) {
            return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
        }

        $data = [];
        return new HtmlContent('login/login', $data);
    }

    public function getGoogleLoginRedirect(): IRedirect
    {
        $state = bin2hex(random_bytes(16));

        $this->request->session()->set('oauth_state', $state);

        $oAuth = new GoogleOAuth();
        $url = $oAuth->getDialogUrl($state, $this->request->getBase() . '/' . \Container::$routeCollection->getRoute('login-google-action')->generateLink());

        return new Redirect($url, IRedirect::TEMPORARY);
    }

    public function getSignupForm()
    {
        if ($this->request->user() !== null) {
            return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
        }

        if ($this->request->session()->has('tmp_user_data')) {
            $tmpUserData = $this->request->session()->get('tmp_user_data');

            $data = ['email' => $tmpUserData['email']];
        } else {
            $data = [];
        }

        return new HtmlContent('login/signup', $data);
    }

    public function getSignupSuccess(): IContent
    {
        $data = [];
        return new HtmlContent('login/signup_success', $data);
    }

    public function getSignupWithGoogleForm()
    {
        if ($this->request->user() !== null) {
            return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
        }

        if (!$this->request->session()->has('google_user_data')) {
            return new Redirect(\Container::$routeCollection->getRoute('login-google')->generateLink(), IRedirect::TEMPORARY);
        }

        $userData = $this->request->session()->get('google_user_data');

        $user = $this->userRepository->getByEmail($userData['email']);

        $data = ['found' => $user !== null, 'email' => $userData['email']];
        return new HtmlContent('login/google_signup', $data);
    }

    public function login(): IContent
    {
        if ($this->request->user() !== null) {
            $data = ['success' => true];
            return new JsonContent($data);
        }

        $user = $this->userRepository->getByEmail($this->request->post('email'));

        if ($user === null) {
            if (strlen($this->request->post('password')) < 6) {
                $data = ['error' => ['errorText' => 'The given password is too short. Please choose a password that is at least 6 characters long!']];
                return new JsonContent($data);
            }

            $tmpUser = new User();
            $tmpUser->setPlainPassword($this->request->post('password'));

            $this->request->session()->set('tmp_user_data', ['email' => $this->request->post('email'), 'password_hashed' => $tmpUser->getPassword()]);

            $data = ['redirect' => ['target' => '/' . \Container::$routeCollection->getRoute('signup')->generateLink()]];
            return new JsonContent($data);
        }

        if (!$user->getActive()) {
            $data = ['error' => ['errorText' => 'User found with the given email address, but the account is not activated. Please check your email and click on the activation link!']];
            return new JsonContent($data);
        }

        if (!$user->checkPassword($this->request->post('password'))) {
            $data = ['error' => ['errorText' => 'The given password is wrong.']];
            return new JsonContent($data);
        }

        $this->request->setUser($user);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function loginWithGoogle()
    {
        if ($this->request->user() !== null) {
            return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
        }

        if ($this->request->query('state') !== $this->request->session()->get('oauth_state')) {
            $data = [];
            return new HtmlContent('login/google_login', $data);
        }

        $oAuth = new GoogleOAuth();
        $tokenData = $oAuth->getToken($this->request->query('code'), $this->request->getBase() . '/' . \Container::$routeCollection->getRoute('login-google-action')->generateLink());

        if (!isset($tokenData['id_token'])) {
            $data = [];
            return new HtmlContent('login/google_login', $data);
        }

        $jwtParser = new JwtParser($tokenData['id_token']);
        $userData = $jwtParser->getPayload();

        if (!$userData['email_verified']) {
            $data = [];
            return new HtmlContent('login/google_login', $data);
        }

        $user = $this->userRepository->getByGoogleSub($userData['sub']);

        if ($user === null) {
            $this->request->session()->set('google_user_data', $userData);

            return new Redirect(\Container::$routeCollection->getRoute('signup-google')->generateLink(), IRedirect::TEMPORARY);
        }

        $this->request->setUser($user);

        return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
    }

    public function logout(): IRedirect
    {
        $this->request->setUser(null);

        return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
    }

    public function signup(): IContent
    {
        if ($this->request->user() !== null) {
            $data = ['redirect' => ['target' => '/' . \Container::$routeCollection->getRoute('home')->generateLink()]];
            return new JsonContent($data);
        }

        $user = $this->userRepository->getByEmail($this->request->post('email'));

        if ($user !== null) {
            if ($user->getActive()) {
                if (!$user->checkPassword($this->request->post('password'))) {
                    $data = ['error' => ['errorText' => 'There is a user already registered with the given email address, but the given password is wrong.']];
                    return new JsonContent($data);
                }

                $this->request->setUser($user);

                $data = ['redirect' => ['target' => '/' . \Container::$routeCollection->getRoute('index')->generateLink()]];
            } else {
                $data = ['error' => ['errorText' => 'There is a user already registered with the given email address. Please check your email and click on the activation link!']];
            }
            return new JsonContent($data);
        }

        if (filter_var($this->request->post('email'), FILTER_VALIDATE_EMAIL) === false) {
            $data = ['error' => ['errorText' => 'The given email address is not valid.']];
            return new JsonContent($data);
        }

        if ($this->request->session()->has('tmp_user_data')) {
            $tmpUserData = $this->request->session()->get('tmp_user_data');

            $tmpUser = new User();
            $tmpUser->setPassword($tmpUserData['password_hashed']);

            if (!$tmpUser->checkPassword($this->request->post('password'))) {
                $data = ['error' => ['errorText' => 'The given passwords do not match.']];
                return new JsonContent($data);
            }
        } else {
            if (strlen($this->request->post('password')) < 6) {
                $data = ['error' => ['errorText' => 'The given password is too short. Please choose a password that is at least 6 characters long!']];
                return new JsonContent($data);
            }

            if ($this->request->post('password') !== $this->request->post('password_confirm')) {
                $data = ['error' => ['errorText' => 'The given passwords do not match.']];
                return new JsonContent($data);
            }
        }

        $user = new User();
        $user->setEmail($this->request->post('email'));
        $user->setPlainPassword($this->request->post('password'));

        \Container::$dbConnection->startTransaction();

        $this->pdm->saveToDb($user);

        $token = hash('sha256', serialize($user) . random_bytes(10) . microtime());

        $confirmation = new UserConfirmation();
        $confirmation->setUser($user);
        $confirmation->setToken($token);

        $this->pdm->saveToDb($confirmation);

        \Container::$dbConnection->commit();

        $this->sendConfirmationEmail($user->getEmail(), $token);

        $this->request->session()->delete('tmp_user_data');

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function signupWithGoogle(): IContent
    {
        if ($this->request->user() !== null) {
            $data = ['success' => true];
            return new JsonContent($data);
        }

        $userData = $this->request->session()->get('google_user_data');

        $user = $this->userRepository->getByEmail($userData['email']);

        if ($user === null) {
            $sendWelcomeEmail = true;

            $user = new User();
            $user->setEmail($userData['email']);
        } else {
            $sendWelcomeEmail = false;
        }

        $user->setActive(true);
        $user->setGoogleSub($userData['sub']);

        $this->pdm->saveToDb($user);

        if ($sendWelcomeEmail) {
            $this->sendWelcomeEmail($user->getEmail());
        }

        $this->request->session()->delete('google_user_data');
        $this->request->setUser($user);

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function resetSignup(): IContent
    {
        $this->request->session()->delete('tmp_user_data');

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function resetGoogleSignup(): IContent
    {
        $this->request->session()->delete('google_user_data');

        $data = ['success' => true];
        return new JsonContent($data);
    }

    public function activate()
    {
        if ($this->request->user() !== null) {
            return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
        }

        $confirmation = $this->userConfirmationRepository->getByToken($this->request->query('token'));

        if ($confirmation === null) {
            $data = [];
            return new HtmlContent('login/activate', $data);
        }

        \Container::$dbConnection->startTransaction();

        $this->pdm->deleteFromDb($confirmation);

        $user = $this->userRepository->getById($confirmation->getUserId());
        $user->setActive(true);

        $this->pdm->saveToDb($user);

        \Container::$dbConnection->commit();

        $this->request->setUser($user);

        return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
    }

    public function cancel()
    {
        if ($this->request->user() !== null) {
            return new Redirect(\Container::$routeCollection->getRoute('index')->generateLink(), IRedirect::TEMPORARY);
        }

        $confirmation = $this->userConfirmationRepository->getByToken($this->request->query('token'));

        if ($confirmation === null) {
            $data = ['success' => false];
            return new HtmlContent('login/cancel', $data);
        }

        \Container::$dbConnection->startTransaction();

        $this->pdm->deleteFromDb($confirmation);

        $user = $this->userRepository->getById($confirmation->getUserId());

        $this->pdm->deleteFromDb($user);

        \Container::$dbConnection->commit();

        $data = ['success' => true];
        return new HtmlContent('login/cancel', $data);
    }

    private function sendConfirmationEmail(string $email, string $token): void
    {
        $mail = new Mail();
        $mail->addRecipient($email);
        $mail->setSubject('Welcome to ' . $_ENV['APP_NAME'] . ' - Activate your account');
        $mail->setBodyFromTemplate('signup', [
            'EMAIL' => $email,
            'ACTIVATE_LINK' => $this->request->getBase() . '/'. \Container::$routeCollection->getRoute('signup.activate')->generateLink(['token' => $token]),
            'CANCEL_LINK' => $this->request->getBase() . '/' . \Container::$routeCollection->getRoute('signup.cancel')->generateLink(['token' => $token]),
        ]);
        $mail->send();
    }

    private function sendWelcomeEmail(string $email): void
    {
        $mail = new Mail();
        $mail->addRecipient($email);
        $mail->setSubject('Welcome to ' . $_ENV['APP_NAME']);
        $mail->setBodyFromTemplate('signup-noconfirm', [
            'EMAIL' => $email,
        ]);
        $mail->send();
    }
}
