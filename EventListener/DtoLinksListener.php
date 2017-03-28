<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollectionInterface;
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
    /** @var ContainerInterface */
    private $linksEnabled;
    /** @var ContainerInterface */
    private $container;

    /**
     * DtoLinksListener constructor.
     * @param RequestManager $requestManager
     * @param DtoLinksManager $linksManager
     * @param bool $linksEnabled
     */
    public function __construct(RequestManager $requestManager, DtoLinksManager $linksManager, bool $linksEnabled)
    {
        $this->requestManager = $requestManager;
        $this->linksManager = $linksManager;
        $this->linksEnabled = $linksEnabled;
    }

    /**
     * @param ApiEvent $apiEvent
     */
    public function onPostDtoEncode(ApiEvent $apiEvent)
    {
        $dto = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_CREATE_DTO
            || !$dto instanceof DtoInterface
            || !$this->linksEnabled
            || !$this->requestManager->isLinksRequired()
        ) {
            return;
        }
        $this->setExpressionLangVars();

        $this->processDtoLinks($dto);
    }

    /**
     * @param ApiEvent $apiEvent
     */
    public function onPostDtoCollectionEncode(ApiEvent $apiEvent)
    {
        $dto = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_CREATE_DTO_COLLECTION
            || !$dto instanceof DtoCollectionInterface
            || $this->requestManager->isLinksRequired()
        ) {
            return;
        }

        $this->setExpressionLangVars();
        foreach ($dto as $dtoItem) {
            $this->processDtoLinks($dtoItem);
        }
    }

    /**
     * @param ContainerInterface $container
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

    /**
     * @param Dto $dto
     */
    private function processDtoLinks(Dto $dto)
    {
        $this->linksManager->processLinks($dto);
    }
}
