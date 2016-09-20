<?php

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFilterManagerInterface;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * ApiFiltersListener constructor.
     * @param RequestManager $requestManager
     * @param ApiFilterManagerInterface $apiFilterManager
     */
    public function __construct(RequestManager $requestManager, ApiFilterManagerInterface $apiFilterManager)
    {
        $this->requestManager = $requestManager;
        $this->apiFilterManager = $apiFilterManager;
    }

    /**
     * @param FilterControllerEvent $filterControllerArgumentsEvent
     */
    public function onKernelController(FilterControllerEvent $filterControllerArgumentsEvent)
    {
        $request = $filterControllerArgumentsEvent->getRequest();
        $route = $request->get('_route');
        // check if route match ***_list pattern
        if ($request->getMethod() !== Request::METHOD_GET
            || strpos($route, '_list') !== (strlen($route) - self::LIST_SUFFIX_LENGTH)
        ) {
            return;
        }

        $filtersCollection = $this->apiFilterManager->parse($this->requestManager->getApiFilters());
        $filterControllerArgumentsEvent->getRequest()->attributes->set('apiFilters', $filtersCollection);
    }
}
