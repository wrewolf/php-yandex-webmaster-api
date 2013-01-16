<?php
namespace Yandex;

class ClientAbstract implements ClientInterface
{
    protected $clientId;

    protected $clientSecret;

    /**
     * @var \Buzz\Message\Response
     */
    protected $latestResponse;

    /**
     * @var \Buzz\Browser
     */
    protected $httpClient;

    public function __construct($clientId, $clientSecret, \Buzz\Browser $httpClient)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->httpClient = $httpClient;
    }

    /**
     * @return \Buzz\Browser
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

}