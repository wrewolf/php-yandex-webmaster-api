<?php
namespace Yandex\Auth;

class Token
{

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \DateTime
     */
    protected $expires_at;

    public function __construct(\stdClass $data)
    {
        $this->token = $data->access_token;
        $this->expires_at = new \DateTime('@' . (time() + $data->expires_in));
        $this->type = $data->token_type;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

}