<?php
namespace Yandex;

use Symfony\Component\DomCrawler\Crawler;

use Yandex\Exception\ErrorException;

class Webmaster extends ClientAbstract
{

    const RESOURCE_STATS = 'stats';
    const RESOURCE_VERIFY = 'verify';
    const RESOURCE_EXCLUDED = 'excluded';
    const RESOURCE_INDEXED = 'indexed';
    const RESOURCE_LINKS = 'links';
    const RESOURCE_TOPS = 'tops';

    /**
     * User token
     * @var string
     */
    protected $token;

    /**
     * user id
     * @var string
     */
    protected $uid;

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
     * Get auth token for current user
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * send API request with OAuth token attached in headers
     *
     * @param $url
     *
     * @return \Buzz\Response
     */
    public function request($url, $type = 'get', $content = null)
    {
        if (empty($this->token)) {
            throw new ErrorException('You must set user token first');
        }
        $authHeader = array(
            'Authorization' => 'OAuth ' . $this->token,
        );
        if ($type == 'post') {
            $response = $this->httpClient->post($url, $authHeader, $content);
        } else {
            $response = $this->httpClient->get($url, $authHeader);
        }
        // TODO: add proper status code handling
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
        $this->httpClient->getClient()->setMaxRedirects(0);
        $this->latestResponse = $this->request($url);
        if ($this->latestResponse->getStatusCode() != 302) {
            throw new ErrorException('Request error');
        }
        $parts = explode('/', $this->latestResponse->getHeader('location'));
        $this->uid = end($parts);
        if (empty($this->uid)) {
            throw new ErrorException('UID was not resolved');
        }
        return $this->uid;
    }

    public function getHostListUrl($uid = null)
    {
        if (empty($uid)) {
            $uid = $this->getUid();
        }
        $url = 'https://webmaster.yandex.ru/api/' . $uid;
        $this->latestResponse = $this->request($url);
        if ($this->latestResponse->getStatusCode() != 200) {
            throw new ErrorException('Request error');
        }

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
        if ($this->latestResponse->getStatusCode() != 200) {
            throw new ErrorException('Request error');
        }

        return new \SimpleXMLElement($this->latestResponse->getContent());
    }

    public function getHostResourcesLinks($url)
    {
        $this->latestResponse = $this->request($url);
        if ($this->latestResponse->getStatusCode() != 200) {
            throw new ErrorException('Request error');
        }

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
        if ($this->latestResponse->getStatusCode() != 200) {
            throw new ErrorException('Request error');
        }

        return new \SimpleXMLElement($this->latestResponse->getContent());
    }

    /**
     * @param $name
     *
     * @return string Host url
     * @throws Exception\ErrorException
     */
    public function addHost($name)
    {
        $url = $this->getHostListUrl();
        $content = '<host>
          <name>' . $name . '</name>
        </host>';
        $this->latestReponse = $this->request($url, 'post', $content);
        if ($this->latestResponse->getStatusCode() != 201) {
            throw new ErrorException('Request error');
        }
        return $this->latestResponse->getHeader('location');
    }

}
