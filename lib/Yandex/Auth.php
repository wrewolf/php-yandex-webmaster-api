<?php
namespace Yandex;

use Yandex\Exception\ErrorException;

class Auth
{
    protected $clientId;

    protected $clientSecret;

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

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getAuthUrl($responseType = 'code', $popup = false, $state = null)
    {
        if (!in_array($responseType, array('token', 'code'))) {
            throw new \InvalidArgumentException('Invalid value for response_type');
        }
        $url = 'https://oauth.yandex.ru/authorize?response_type=' . $responseType . '&client_id=' . $this->clientId;
        if ($popup) {
            $url .= '&display=popup';
        }
        if (! empty($state)) {
            $url .= '&state=' . $state;
        }
        return $url;
    }

    /**
     * Retrieves user token information
     * TODO: basic http auth implementation
     *
     * @param $code
     */
    public function getAuthToken($code)
    {
        $url = 'https://oauth.yandex.ru/token';
        $content = 'grant_type=authorization_code'
            . '&code=' . $code
            . '&client_id=' . $this->clientId
            . '&client_secret=' . $this->clientSecret;
        $response = $this->httpClient->post($url, array(), $content);
        if (200 != $response->getStatusCode()) {
            throw new ErrorException('Request error');
        }
        $data = json_decode($response->getContent());
        if (null == $data) {
            throw new ErrorException('Invalid token data');
        }
        return $data;
    }
}