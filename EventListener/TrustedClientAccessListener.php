<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Model\TrustedUser;
use Mell\Bundle\SimpleDtoBundle\Security\Provider\TrustedClientProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class TrustedClientAccessListener
 * @package Mell\Bundle\SimpleDtoBundle\EventListener
 */
class TrustedClientAccessListener
{
    const ACCESS_FULL = '*';
    
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * TrustedClientAccessListener constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user->hasRole(TrustedClientProvider::ROLE_TRUSTED_USER)
            || !$user instanceof TrustedUser
            || in_array(self::ACCESS_FULL, $user->getAccess())
        ) {
            return;
        }
        if (!in_array($event->getRequest()->get('_route'), $user->getAccess())) {
            throw new AccessDeniedHttpException('Access denied.');
        }

    }
}
