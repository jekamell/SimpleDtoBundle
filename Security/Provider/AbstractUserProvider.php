<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Provider;

use Mell\Bundle\SimpleDtoBundle\Services\Jwt\JwtManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class AbstractProvider
 * @package Mell\Bundle\SimpleDtoBundle\Security\Authenticator
 */
abstract class AbstractUserProvider implements UserProviderInterface
{
    /** @var JwtManagerInterface */
    protected $jwtManager;
    /** @var string */
    protected $publicKeyPath;

    /**
     * @param TokenInterface $token
     * @return bool
     */
    abstract public function supportToken(TokenInterface $token);

    /**
     * @param JwtManagerInterface $jwtManager
     */
    public function setJwtManager(JwtManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     * @param string $publicKeyPath
     */
    public function setPublicKeyPath(string $publicKeyPath)
    {
        $this->publicKeyPath = $publicKeyPath;
    }

    /**
     * @param string $apiKey
     * @return array|null
     */
    public function getPayload($apiKey)
    {
        $publicKey = $this->getPublicKey();
        if (!$this->jwtManager->isValid($apiKey, $publicKey)) {
            return null;
        }

        return $this->jwtManager->encode($apiKey, $this->getPublicKey());
    }

    /**
     * @return resource
     */
    private function getPublicKey()
    {
        if (!is_file($this->publicKeyPath)) {
            throw new FileNotFoundException($this->publicKeyPath);
        }

        return openssl_pkey_get_public(file_get_contents($this->publicKeyPath));
    }
}
