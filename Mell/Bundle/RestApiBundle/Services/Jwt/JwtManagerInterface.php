<?php

namespace Mell\Bundle\RestApiBundle\Services\Jwt;

interface JwtManagerInterface
{
    const ALG_DEFAULT = 'RS256';

    /**
     * @param array $payload
     * @param resource $privateKey
     * @param integer $ttl
     * @param string $algorithm
     * @return string
     */
    public function decode(array $payload, $privateKey, $ttl, $algorithm = self::ALG_DEFAULT);

    /**
     * @param string $token
     * @param resource $publicKey
     * @return array
     */
    public function encode($token, $publicKey);
}
