<?php
namespace Yandex;

use Symfony\Component\DomCrawler\Crawler;

use Yandex\Exception\ErrorException;

class Webmaster
{

    const RESOURCE_STATS = 'stats';
    const RESOURCE_VERIFY = 'verify';
    const RESOURCE_EXCLUDED = 'excluded';
    const RESOURCE_INDEXED = 'indexed';
    const RESOURCE_LINKS = 'links';
    const RESOURCE_TOPS = 'tops';

    protected $clientId;

    protected $clientSecret;

    protected $token;

    protected $uid;

    /**
     * @var \Buzz\Message\Response
     */
    protected $latestResponse;

    /**
     * @var \Buzz\Browser
     */
    protected $buzz;

    public function __construct($clientId, $clientSecret, \Buzz\Browser $buzz)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->buzz = $buzz;
    }



    /**
     * Set auth token for current user
     *
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * send API request with OAuth token attached in headers
     *
     * @param $url
     *
     * @return \Buzz\Response
     */
    protected function request($url)
    {
        $response = $this->buzz->get($url, array(
            'Authorization' => 'OAuth ' . $this->token,
        ));
        // TODO: add proper status code handling
        if (!in_array($response->getStatusCode(), array(200, 302))) {
            throw new ErrorException('Request error');
        }
        return $response;
    }

    /**
     *
     * @return \Buzz\Message\Response
     */
    public function getLatestResponse()
    {
        return $this->latestResponse;
    }

    public function getUid()
    {
        $url = 'https://webmaster.yandex.ru/api/me';
        $this->buzz->getClient()->setMaxRedirects(0);
        $this->latestResponse = $this->request($url);
        $parts = explode('/', $this->latestResponse->getHeader('location'));
        $this->uid = end($parts);
        if (empty($this->uid)) {
            throw new ErrorException('UID was not resolved');
        }
        return $this->uid;
    }

    public function getHostListUrl()
    {
        $url = 'https://webmaster.yandex.ru/api/' . $this->getUid();
        $this->latestResponse = $this->request($url);

        $xml = new \SimpleXMLElement($this->latestResponse->getContent());
        $hostListUrl = (string) $xml->workspace->collection['href'];
        if (empty($hostListUrl)) {
            throw new ErrorException('Host list url was not resolved');
        }
        return $hostListUrl;
    }

    public function getHostList($url = null)
    {
        if (empty($url)) {
            $url = $this->getHostListUrl();
        }

        $this->latestResponse = $this->request($url);
        return new \SimpleXMLElement($this->latestResponse->getContent());
    }

    public function getHostResourcesLinks($url)
    {
        $this->latestResponse = $this->request($url);
        $xml = new \SimpleXMLElement($this->latestResponse->getContent());
        $links = array();
        foreach ($xml->link as $link) {
            $links[] = (string) $link['href'];
        }

        $namedLinks = array();
        foreach ($links as $link) {
            $parts = explode('/', $link);
            $resource = end($parts);
            $namedLinks[$resource] = $link;
        }

        return $namedLinks;
    }

    public function getHostStats($url)
    {
        $this->latestResponse = $this->request($url);
        return new \SimpleXMLElement($this->latestResponse->getContent());
    }

}