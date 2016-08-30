<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoExpandsManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;

/**
 * Class DtoExpandsListener
 * @package Mell\Bundle\SimpleDtoBundle\EventListener
 */
class DtoExpandsListener
{
    /** @var RequestManager */
    private $requestManager;
    /** @var DtoHelper */
    private $dtoHelper;
    /** @var DtoExpandsManager */
    private $expandsManager;

    /**
     * DtoExpandsListener constructor.
     * @param RequestManager $requestManager
     * @param DtoHelper $dtoHelper
     * @param DtoExpandsManager $expandsManager
     */
    public function __construct(RequestManager $requestManager, DtoHelper $dtoHelper, DtoExpandsManager $expandsManager)
    {
        $this->requestManager = $requestManager;
        $this->dtoHelper = $dtoHelper;
        $this->expandsManager = $expandsManager;
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

        $expands = $this->requestManager->getExpands();
        if (empty($expands)) {
            return;
        }

        $this->processDtoExpands($dto, $expands);
    }

    /**
     * @param ApiEvent $apiEvent
     */
    public function onPostDtoCollectionEncode(ApiEvent $apiEvent)
    {
        $dto = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_CREATE_DTO_COLLECTION
            || !$dto instanceof DtoCollectionInterface
        ) {
            return;
        }

        if (empty($this->requestManager->getExpands())) {
            return;
        }

        foreach ($dto as $dtoItem) {
            $this->processDtoExpands($dtoItem, $this->requestManager->getExpands());
        }
    }

    /**
     * @param $dto
     * @param $expands
     * @return DtoInterface
     */
    private function processDtoExpands($dto, $expands)
    {
        return $this->expandsManager->processExpands($dto, $expands, $this->dtoHelper->getDtoConfig());
    }
}
