<?php
namespace Yandex;

use Yandex\Exception\ErrorException;
use Yandex\Auth\Token;

class Auth
{
    const RESPONSE_TYPE_CODE = 'code';
    const RESPONSE_TYPE_TOKEN = 'token';

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

    /**
     * @return \Buzz\Browser
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param string $responseType
     * @param bool $popup
     * @param null $state
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getAuthUrl($responseType = self::RESPONSE_TYPE_CODE, $popup = false, $state = null)
    {
        if (!in_array($responseType, array(self::RESPONSE_TYPE_TOKEN, self::RESPONSE_TYPE_CODE))) {
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
     *
     * @return \Yandex\Auth\Token
     * @throws Exception\ErrorException
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
        if (! empty($data->error)) {
            throw new ErrorException($data->error);
        }
        return new Token($data);
    }
}