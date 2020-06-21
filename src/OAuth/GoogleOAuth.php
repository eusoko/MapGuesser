<?php namespace MapGuesser\OAuth;

use MapGuesser\Http\Request;

class GoogleOAuth
{
    private static $dialogUrlBase = 'https://accounts.google.com/o/oauth2/v2/auth';

    private static $tokenUrlBase = 'https://oauth2.googleapis.com/token';

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

        $request = new Request(self::$tokenUrlBase, Request::HTTP_POST);
        $request->setQuery($tokenParams);
        $response = $request->send();

        return json_decode($response->getBody(), true);
    }
}
