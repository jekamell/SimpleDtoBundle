<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoLinksManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DtoLinksListener
 * @package Mell\Bundle\SimpleDtoBundle\EventListener
 */
class DtoLinksListener implements ContainerAwareInterface
{
    /** @var RequestManager */
    private $requestManager;
    /** @var DtoLinksManager */
    private $linksManager;
    /** @var DtoHelper */
    private $dtoHelper;
    /** @var ContainerInterface */
    private $container;

    /**
     * DtoLinksListener constructor.
     * @param RequestManager $requestManager
     * @param DtoLinksManager $linksManager
     * @param DtoHelper $dtoHelper
     */
    public function __construct(RequestManager $requestManager, DtoLinksManager $linksManager, DtoHelper $dtoHelper)
    {
        $this->requestManager = $requestManager;
        $this->linksManager = $linksManager;
        $this->dtoHelper = $dtoHelper;
    }

    /**
     * @param ApiEvent $apiEvent
     */
    public function onPostDtoEncode(ApiEvent $apiEvent)
    {
        $dto = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_CREATE_DTO || !$dto instanceof DtoInterface) {
            return;
        }

        if (!$this->requestManager->isLinksRequired()) {
            return;
        }

        $this->setExpressionLangVars();

        $this->linksManager->processLinks($dto, $this->dtoHelper->getDtoConfig());
    }

    /**
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Assign expression variables for DtoLinksManager::expressionLanguage
     */
    private function setExpressionLangVars()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $vars = [
            'user' => $user,
            'roles' => $user ? $user->getRoles() : [],
            'request' => $this->container->get('request_stack')->getCurrentRequest(),
            'trust_resolver' => $this->container->get('security.authentication.trust_resolver'),
            'auth_checker' => $this->container->get('security.authorization_checker'),
        ];

        $this->linksManager->setExpressionVars($vars);
    }
}
