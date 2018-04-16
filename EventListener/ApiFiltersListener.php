<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFilterManagerInterface;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFiltersManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ApiFiltersListener
 */
class ApiFiltersListener
{
    const LIST_SUFFIX_LENGTH = 5;

    /** @var RequestManager */
    protected $requestManager;
    /** @var ApiFilterManager */
    protected $apiFilterManager;
    /** @var RouterInterface */
    protected $router;

    /**
     * ApiFiltersListener constructor.
     * @param RequestManager $requestManager
     * @param ApiFilterManager $apiFilterManager
     * @param RouterInterface $router
     */
    public function __construct(
        RequestManager $requestManager,
        ApiFiltersManager $apiFilterManager,
        RouterInterface $router
    ) {
        $this->requestManager = $requestManager;
        $this->apiFilterManager = $apiFilterManager;
        $this->router = $router;
    }

    /**
     * @param FilterControllerEvent $filterControllerArgumentsEvent
     */
    public function onKernelController(FilterControllerEvent $filterControllerArgumentsEvent): void
    {
        try {
            $routeParams = $this->router->matchRequest($filterControllerArgumentsEvent->getRequest());
        } catch (\Exception $e) {
            return;
        }
        if (!$filters = $routeParams['filters'] ?? null) {
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
