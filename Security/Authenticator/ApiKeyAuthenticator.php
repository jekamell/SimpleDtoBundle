<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Authenticator;

use Mell\Bundle\SimpleDtoBundle\Security\Model\UserCredentials;
use Mell\Bundle\SimpleDtoBundle\Security\Provider\AbstractUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

/**
 * Class ApiKeyAuthenticator
 * @package Mell\Bundle\SimpleDtoBundle\Security\Authenticator
 */
class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    const HEADER_AUTH = 'Authorization';

    /** @var AbstractUserProvider[] */
    protected $providers = [];

    /**
     * ApiKeyAuthenticator constructor.
     * @param UserProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @param string $providerKey
     * @return PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $apiKey = $token->getCredentials()->getToken();
        foreach ($this->providers as $userProvider) {
            if (!$userProvider->supportToken($token)) {
                continue;
            }

            $payload = $userProvider->getPayload($apiKey);
            if (!isset($payload['username']) || !($user = $userProvider->loadUserByUsername($payload['username']))) {
                throw new AuthenticationException(sprintf('User %s was not found', $payload['username'] ?? ''));
            }

            return new PreAuthenticatedToken(
                $user,
                $apiKey,
                $providerKey,
                $user->getRoles()
            );
        }
    }

    /**
     * @param TokenInterface $token
     * @param $providerKey
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * @param Request $request
     * @param $providerKey
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $header = $request->headers->get(self::HEADER_AUTH);
        if (preg_match('/^(Bearer|Trusted)\sToken=(.*)/', $header, $m)) {
            $credentials = new UserCredentials($m[2], $m[1]); //token, type

            return new PreAuthenticatedToken('anon.', $credentials, $providerKey);
        }

        throw new AuthenticationException('Failed to find authorization credentials');
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['_error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }
}
