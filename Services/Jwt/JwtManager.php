<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\Jwt;

use Namshi\JOSE\JWS;
use Namshi\JOSE\SimpleJWS;

class JwtManager implements JwtManagerInterface
{
    /**
     * @param array $payload
     * @param resource $privateKey
     * @param int $ttl
     * @param string $algorithm
     * @return string
     */
    public function decode(array $payload, $privateKey, $ttl = 86400, $algorithm = JwtManagerInterface::ALG_DEFAULT)
    {
        $now = new \DateTime();
        $jws = new JWS(['alg' => $algorithm]);
        $jws->setPayload(array_merge($payload, ['exp' => $now->getTimestamp() + $ttl]));
        $jws->sign($privateKey);

        return $jws->getTokenString();
    }

    /**
     * @param string $token
     * @param string $publicKey
     * @return array
     */
    public function encode($token, $publicKey)
    {
        try {
            /** @var SimpleJWS $jws */
            $jws = SimpleJWS::load($token);
            if ($jws->isValid($publicKey)) {

                return $jws->getPayload();
            }
        } catch (\Exception $e) {
        }

        return [];
    }

    /**
     * @param string $token
     * @param resource $publicKey
     * @return bool
     */
    public function isValid($token, $publicKey)
    {
        /** @var SimpleJWS $jws */
        $jws = SimpleJWS::load($token);

        return $jws->isValid($publicKey);
    }
}
