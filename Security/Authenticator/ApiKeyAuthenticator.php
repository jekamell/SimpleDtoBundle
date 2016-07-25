<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Authenticator;

use Doctrine\ORM\EntityManager;
use Mell\Bundle\SimpleDtoBundle\Services\Jwt\JwtManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    const HEADER_AUTH = 'Authorization';

    const MESSAGE_AUTH_FAILED = 'Authentication failed';

    /** @var EntityManager */
    protected $entityManager;
    /** @var JwtManagerInterface */
    protected $jwtManager;
    /** @var string */
    protected $publicKeyPath;
    /** @var array */
    protected $payload;

    /**
     * ApiKeyAuthenticator constructor.
     * @param EntityManager $entityManager
     * @param JwtManagerInterface $jwtManager
     * @param string $publicKeyPath
     */
    public function __construct(EntityManager $entityManager, JwtManagerInterface $jwtManager, $publicKeyPath)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->publicKeyPath = $publicKeyPath;
    }

    /**
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['_error' => self::MESSAGE_AUTH_FAILED], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @return mixed|null
     */
    public function getCredentials(Request $request)
    {
        $authHeader = $request->headers->get(self::HEADER_AUTH);
        if (preg_match('/^(Bearer)\sToken=(.*)$/', $authHeader, $m)) {
            return [
                'type' => $m[1],
                'token' => $m[2],
            ];
        }
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!empty($credentials) && isset($credentials['token'])) {

            return $this->jwtManager->isValid($credentials['token'], $this->getPublicKey());
        }
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface|null
     * @throws AuthenticationException
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if ($payload = $this->getPayload($credentials['token'])) {

            return $this->entityManager->getRepository('AppBundle:User')->findOneBy(['email' => $payload['username']]);
        }
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['_error' => self::MESSAGE_AUTH_FAILED], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey The provider (i.e. firewall) key
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // continue current request
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * @param $token
     * @return array
     */
    protected function getPayload($token)
    {
        if (empty($this->payload)) {
            $this->payload = $this->jwtManager->encode($token, $this->getPublicKey());
        }

        return $this->payload;
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
