<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Helpers\DtoHelper;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoCollection;
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
    public function onPostDtoEncode(ApiEvent $apiEvent): void
    {
        $dto = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_CREATE_DTO || !$dto instanceof Dto) {
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
    public function onPostDtoCollectionEncode(ApiEvent $apiEvent): void
    {
        $dto = $apiEvent->getData();
        if ($apiEvent->getAction() !== ApiEvent::ACTION_CREATE_DTO_COLLECTION || !$dto instanceof DtoCollection) {
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
     * @return Dto
     */
    private function processDtoExpands($dto, $expands): Dto
    {
        return $this->expandsManager->processExpands($dto, $expands);
    }
}
