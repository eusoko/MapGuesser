<?php namespace MapGuesser\OAuth;

use MapGuesser\Interfaces\Http\IRequest;

class GoogleOAuth
{
    private static $dialogUrlBase = 'https://accounts.google.com/o/oauth2/v2/auth';

    private static $tokenUrlBase = 'https://oauth2.googleapis.com/token';

    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function getDialogUrl(string $state, string $redirectUrl): string
    {
        $oauthParams = [
            'response_type' => 'code',
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID'],
            'scope' => 'openid email',
            'redirect_uri' => $redirectUrl,
            'state' => $state,
            'nonce' => hash('sha256', random_bytes(10) . microtime()),
        ];

        return self::$dialogUrlBase . '?' . http_build_query($oauthParams);
    }

    public function getToken(string $code, string $redirectUrl)
    {
        $tokenParams = [
            'code' => $code,
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_OAUTH_CLIENT_SECRET'],
            'redirect_uri' => $redirectUrl,
            'grant_type' => 'authorization_code',
        ];

        $this->request->setUrl(self::$tokenUrlBase);
        $this->request->setMethod(IRequest::HTTP_POST);
        $this->request->setQuery($tokenParams);
        $response = $this->request->send();

        return json_decode($response->getBody(), true);
    }
}
