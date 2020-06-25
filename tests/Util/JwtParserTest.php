<?php namespace MapGuesser\Tests\Util;

use MapGuesser\Util\JwtParser;
use PHPUnit\Framework\TestCase;

final class JwtParserTest extends TestCase
{
    private JwtParser $jwtParser;

    protected function setUp(): void
    {
        $this->jwtParser = new JwtParser(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c'
        );
    }

    public function testSettingTokenIsTheSameAsCreatingWithToken(): void
    {
        $jwtParser2 = new JwtParser();
        $jwtParser2->setToken(
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c'
        );

        $this->assertEquals($this->jwtParser, $jwtParser2);
    }

    public function testCanParseTokenHeader(): void
    {
        $this->assertEquals([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ], $this->jwtParser->getHeader());
    }

    public function testCanParseTokenPayload(): void
    {
        $this->assertEquals([
            'sub' => '1234567890',
            'name' => 'John Doe',
            'iat' => 1516239022
        ], $this->jwtParser->getPayload());
    }

    public function testCanParseTokenSignature(): void
    {
        $this->assertEquals(
            '49f94ac7044948c78a285d904f87f0a4c7897f7e8f3a4eb2255fda750b2cc397',
            bin2hex($this->jwtParser->getSignature())
        );
    }
}
