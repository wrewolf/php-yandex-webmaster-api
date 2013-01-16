<?php

namespace Yandex\Tests;

use Yandex\Webmaster;
use Buzz\Message\Response;

class WebmasterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testPassHttpClientToConstructor()
    {
        $webmaster = new Webmaster('test', 'test', $this->getHttpClientMock());

        $this->assertInstanceOf('Buzz\Browser', $webmaster->getHttpClient());
    }

    /**
     * @test
     */
    public function testSetToken()
    {
        $webmaster = new Webmaster('test', 'test', $this->getHttpClientMock());
        $webmaster->setToken('ea135929105c4f29a0f5117d2960926f');

        $this->assertEquals('ea135929105c4f29a0f5117d2960926f', $webmaster->getToken());
    }

    /**
     * @test
     */
    public function testGetUid()
    {
        $response = new Response();
        $response->setHeaders(array(
            'HTTP/1.1 200 OK',
            'Location: https://webmaster.yandex.ru/api/123456789'
        ));

        $httpClient = $this->getHttpClientMock();
        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('https://webmaster.yandex.ru/api/me')
            )
            ->will($this->returnValue($response))
        ;
        $httpClient
            ->expects($this->once())
            ->method('getClient')
            ->will(
                $this->returnValue($this->getMock('Buzz\Client\AbstractClient'))
            );

        $webmaster = new Webmaster('test', 'test', $httpClient);
        $webmaster->setToken('ea135929105c4f29a0f5117d2960926f');
        $uid = $webmaster->getUid();

        $this->assertEquals($uid, '123456789');
    }

    /**
     * @test
     */
    public function testGetHostListUrl()
    {
        $response = new Response();
        $response->setHeaders(array('HTTP/1.1 200 OK'));
        $response->setContent('
            <service>
              <workspace>
                <collection href="https://webmaster.yandex.ru/api/123456789/hosts">
                  <title>Host list</title>
                </collection>
              </workspace>
            </service>
        ');

        $httpClient = $this->getHttpClientMock();
        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('https://webmaster.yandex.ru/api/123456789')
            )
            ->will($this->returnValue($response))
        ;

        $webmaster = new Webmaster('test', 'test', $httpClient);
        $webmaster->setToken('ea135929105c4f29a0f5117d2960926f');
        $url = $webmaster->getHostListUrl(123456789);

        $this->assertEquals($url, 'https://webmaster.yandex.ru/api/123456789/hosts');
    }

    private function getHttpClientMock()
    {
        return $this->getMock('Buzz\Browser');
    }

}
