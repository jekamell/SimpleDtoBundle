<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFilterManagerInterface;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class ApiFiltersListener
 * @package Mell\Bundle\SimpleDtoBundle\EventListener
 */
class ApiFiltersListener
{
    const LIST_SUFFIX_LENGTH = 5;

    /** @var RequestManager */
    protected $requestManager;
    /** @var ApiFilterManagerInterface */
    protected $apiFilterManager;
    /** @var Router */
    protected $router;

    /**
     * ApiFiltersListener constructor.
     * @param RequestManager $requestManager
     * @param ApiFilterManagerInterface $apiFilterManager
     * @param Router $router
     */
    public function __construct(RequestManager $requestManager, ApiFilterManagerInterface $apiFilterManager, Router $router)
    {
        $this->requestManager = $requestManager;
        $this->apiFilterManager = $apiFilterManager;
        $this->router = $router;
    }

    /**
     * @param FilterControllerEvent $filterControllerArgumentsEvent
     */
    public function onKernelController(FilterControllerEvent $filterControllerArgumentsEvent)
    {
        try {
            $routeParams = $this->router->matchRequest($filterControllerArgumentsEvent->getRequest());
        } catch (\Exception $e) {
            return;
        }
        if (!$filters = $routeParams['_filters'] ?? null) {
            return;
        }

        $filtersCollection = $this->apiFilterManager->parse($this->requestManager->getApiFilters());
        /** @var ApiFilter $apiFilter */
        foreach ($filtersCollection as $i => $apiFilter) {
            if (!in_array($apiFilter->getParam(), $filters)) {
                $filtersCollection->offsetUnset($i);
            }
        }
        $filterControllerArgumentsEvent->getRequest()->attributes->set('apiFilters', $filtersCollection);
    }
}
