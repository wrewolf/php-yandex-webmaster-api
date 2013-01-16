<?php

namespace Yandex\Tests;

use Yandex\Webmaster;

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

    public function getHttpClientMock()
    {
        return $this->getMock('Buzz\Browser');
    }

}
