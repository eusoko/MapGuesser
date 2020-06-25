<?php

namespace MapGuesser\Tests\OAuth;

use MapGuesser\Interfaces\Http\IRequest;
use MapGuesser\Interfaces\Http\IResponse;
use MapGuesser\OAuth\GoogleOAuth;
use PHPUnit\Framework\TestCase;

final class GoogleOAuthTest extends TestCase
{
    public function testCanCreateDialogUrl(): void
    {
        $_ENV['GOOGLE_OAUTH_CLIENT_ID'] = 'xyz';
        $state = 'random_state_string';
        $redirectUrl = 'http://example.com/oauth';

        $requestMock = $this->getMockBuilder(IRequest::class)
            ->setMethods(['setUrl', 'setMethod', 'setQuery', 'setHeaders', 'send'])
            ->getMock();
        $googleOAuth = new GoogleOAuth($requestMock);

        $dialogUrl = $googleOAuth->getDialogUrl($state, $redirectUrl);
        $dialogUrlParsed = explode('?', $dialogUrl);

        $this->assertEquals('https://accounts.google.com/o/oauth2/v2/auth', $dialogUrlParsed[0]);

        parse_str($dialogUrlParsed[1], $dialogUrlQueryParams);

        $expectedQueryParams = [
            'response_type' => 'code',
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID'],
            'scope' => 'openid email',
            'redirect_uri' => $redirectUrl,
            'state' => $state,
            'nonce' => hash('sha256', random_bytes(10) . microtime()),
        ];

        $this->assertEquals($expectedQueryParams['response_type'], $dialogUrlQueryParams['response_type']);
        $this->assertEquals($expectedQueryParams['client_id'], $dialogUrlQueryParams['client_id']);
        $this->assertEquals($expectedQueryParams['scope'], $dialogUrlQueryParams['scope']);
        $this->assertEquals($expectedQueryParams['redirect_uri'], $dialogUrlQueryParams['redirect_uri']);
        $this->assertEquals($expectedQueryParams['state'], $dialogUrlQueryParams['state']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $dialogUrlQueryParams['nonce']);
    }

    public function testCanRequestToken(): void
    {
        $_ENV['GOOGLE_OAUTH_CLIENT_ID'] = 'abc';
        $_ENV['GOOGLE_OAUTH_CLIENT_SECRET'] = 'xxx';
        $code = 'code_from_google';
        $redirectUrl = 'http://example.com/oauth';

        $requestMock = $this->getMockBuilder(IRequest::class)
            ->setMethods(['setUrl', 'setMethod', 'setQuery', 'setHeaders', 'send'])
            ->getMock();
        $responseMock = $this->getMockBuilder(IResponse::class)
            ->setMethods(['getBody', 'getHeaders'])
            ->getMock();
        $googleOAuth = new GoogleOAuth($requestMock);

        $expectedQueryParams = [
            'code' => $code,
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_OAUTH_CLIENT_SECRET'],
            'redirect_uri' => $redirectUrl,
            'grant_type' => 'authorization_code',
        ];

        $requestMock->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo('https://oauth2.googleapis.com/token'));
        $requestMock->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo(IRequest::HTTP_POST));
        $requestMock->expects($this->once())
            ->method('setQuery')
            ->with($this->equalTo($expectedQueryParams));
        $requestMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($responseMock));
        $responseMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('{"test":"json"}'));

        $token = $googleOAuth->getToken($code, $redirectUrl);

        $this->assertEquals(['test' => 'json'], $token);
    }
}
