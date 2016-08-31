<?php

namespace Mell\Bundle\SimpleDtoBundle\Security\Provider;

use Doctrine\ORM\EntityManager;
use Mell\Bundle\SimpleDtoBundle\Security\Model\UserCredentialsInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class BearerClientProvider
 * @package Mell\Bundle\SimpleDtoBundle\Security\Provider
 */
class BearerClientProvider extends AbstractUserProvider
{
    /** @var EntityManager */
    protected $entityManager;
    /** @var string */
    protected $entityAlias;
    /** @var string */
    protected $usernameField;

    /**
     * BearerClientProvider constructor.
     * @param EntityManager $entityManager
     * @param string $entityAlias
     * @param string $usernameField
     */
    public function __construct(EntityManager $entityManager, $entityAlias, $usernameField)
    {
        $this->entityManager = $entityManager;
        $this->entityAlias = $entityAlias;
        $this->usernameField = $usernameField;
    }

    /**
     * Loads the user for the given username.
     *
     * @param string $username The username
     * @return UserInterface
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $repository = $this->entityManager->getRepository($this->entityAlias);
        if ($repository instanceof UserProviderInterface) {
            return $repository->loadUserByUsername($username);
        }

        return $repository->findOneBy([$this->usernameField => $username]);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * @param UserInterface $user
     * @return UserInterface
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
     * @return bool
     */
    public function supportsClass($class)
    {
        $metadata = $this->entityManager->getClassMetadata($this->entityAlias);

        return $class instanceof $metadata->name;
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function supportToken(TokenInterface $token)
    {
        $credentials = $token->getCredentials();

        return $credentials instanceof UserCredentialsInterface
            && $credentials->getType() === UserCredentialsInterface::TYPE_BEARER;
    }
}
