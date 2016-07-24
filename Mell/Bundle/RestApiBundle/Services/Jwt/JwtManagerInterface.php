<?php

namespace Mell\Bundle\RestApiBundle\Services\Jwt;

interface JwtManagerInterface
{
    /**
     * @param array $payload
     * @param resource $privateKey
     * @param integer $ttl
     * @param string $algorithm
     * @return string
     */
    public function decode(array $payload, $privateKey, $ttl, $algorithm);

    /**
     * @param string $token
     * @param resource $publicKey
     * @return array
     */
    public function encode($token, $publicKey);
}
