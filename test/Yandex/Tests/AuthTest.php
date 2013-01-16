<?php

namespace Yandex\Tests;

use Yandex\Auth;
use Buzz\Message\Response;

class AuthTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testPassHttpClientToConstructor()
    {
        $auth = new Auth('test', 'test', $this->getHttpClientMock());

        $this->assertInstanceOf('Buzz\Browser', $auth->getHttpClient());
    }

    /**
     * @test
     * @dataProvider getAuthCode
     */
    public function testGetAuthTokenGivenCode($code)
    {
        $response = new Response();
        $response->setContent('{"access_token": "ea135929105c4f29a0f5117d2960926f", "expires_in": 2592000}');
        $response->setHeaders(array('HTTP/1.1 200 OK'));

        $httpClient = $this->getHttpClientMock();
        $httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('https://oauth.yandex.ru/token'),
                $this->anything(),
                $this->stringContains($code)
            )
            ->will($this->returnValue($response))
            ;

        $auth = new Auth('test', 'test', $httpClient);
        $token = $auth->getAuthToken($code);

        $this->assertInstanceOf('\Yandex\Auth\Token', $token);
        $this->assertEquals($token->getToken(), 'ea135929105c4f29a0f5117d2960926f');
    }

    public function getAuthCode()
    {
        return array(
            array('123412341'),
            array('563753456'),
        );
    }

    /**
     * @test
     * @dataProvider getAuthUrlData
     */
    public function testGetAuthUrl($responseType, $popup, $state, $url)
    {
        $auth = new Auth('test', 'test', $this->getHttpClientMock());
        $testUrl = $auth->getAuthUrl($responseType, $popup, $state);
        $this->assertEquals($testUrl, $url);
    }

    public function getAuthUrlData()
    {
        return array(
            array('token', false, false, 'https://oauth.yandex.ru/authorize?response_type=token&client_id=test'),
            array('token', true, false, 'https://oauth.yandex.ru/authorize?response_type=token&client_id=test&display=popup'),
            array('code', false, false, 'https://oauth.yandex.ru/authorize?response_type=code&client_id=test'),
            array('code', false, 'anystring', 'https://oauth.yandex.ru/authorize?response_type=code&client_id=test&state=anystring'),
        );
    }

    private function getHttpClientMock()
    {
        return $this->getMock('Buzz\Browser');
    }

}