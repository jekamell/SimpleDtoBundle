<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Model;

/**
 * Class UserCredentials
 * @package Mell\Bundle\SimpleDtoBundle\Security\Model
 */
class UserCredentials implements UserCredentialsInterface
{
    /** @var string */
    private $token;
    /** @var string */
    private $type;

    /**
     * UserCredentials constructor.
     * @param string $token
     * @param string $type
     */
    public function __construct($token, $type)
    {
        $this->token = $token;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
