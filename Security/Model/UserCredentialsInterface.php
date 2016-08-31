<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Model;

/**
 * Interface UserCredentialsInterface
 * @package Mell\Bundle\SimpleDtoBundle\Security\Model
 */
interface UserCredentialsInterface
{
    const TYPE_BEARER = 'Bearer';
    const TYPE_TRUSTED = 'Trusted';

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();
}
