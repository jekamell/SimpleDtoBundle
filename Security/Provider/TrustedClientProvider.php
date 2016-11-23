<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Provider;

use Mell\Bundle\SimpleDtoBundle\Model\TrustedUser;
use Mell\Bundle\SimpleDtoBundle\Security\Model\UserCredentialsInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TrustedClientProvider
 * @package Mell\Bundle\SimpleDtoBundle\Security\Authenticator
 */
class TrustedClientProvider extends AbstractUserProvider
{
    const ROLE_TRUSTED_USER = 'ROLE_TRUSTED_USER';

    /** @var array */
    protected $trustedClients = [];

    /**
     * TrustedClientProvider constructor.
     * @param array $trustedClient
     */
    public function __construct(array $trustedClients)
    {
        $this->trustedClients = $trustedClients;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        foreach ($this->trustedClients as $trustedClient) {
            if ($trustedClient['name'] === $username) {
                return new TrustedUser(
                    $trustedClient['id'],
                    $trustedClient['name'],
                    [self::ROLE_TRUSTED_USER],
                    $trustedClient['access']
                );
            }
        }

        $exception = new UsernameNotFoundException();
        $exception->setUsername($username);

        throw $exception;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {

    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function supportToken(TokenInterface $token)
    {
        $credentials = $token->getCredentials();

        return $credentials instanceof UserCredentialsInterface
            && $credentials->getType() === UserCredentialsInterface::TYPE_TRUSTED;
    }

    /**
     * @param string $apiKey
     * @return array|null
     */
    public function getPayload($apiKey)
    {
        $payload =  parent::getPayload($apiKey);
        if (isset($payload['name'])) {
            $payload['username'] = $payload['name'];
            unset($payload['name']);
        }

        return $payload;
    }
}
