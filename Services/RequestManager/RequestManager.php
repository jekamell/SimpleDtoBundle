<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\RequestManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestManager
{
    /** @var Request */
    protected $request;
    /** @var RequestManagerConfigurator */
    protected $requestManagerConfiguration;

    /**
     * RequestManager constructor.
     *
     * @param RequestStack $requestStack
     * @param RequestManagerConfigurator $requestManagerConfiguration
     */
    public function __construct(RequestStack $requestStack, RequestManagerConfigurator $requestManagerConfiguration)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->requestManagerConfiguration = $requestManagerConfiguration;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        if ($fieldsStr = $this->request->get($this->requestManagerConfiguration->getFieldsParam())) {
            return array_unique(array_map('trim', explode(',', $fieldsStr)));
        }

        return [];
    }

    /**
     * @return array
     */
    public function getExpands()
    {
        if ($expandsStr = $this->request->get($this->requestManagerConfiguration->getExpandsParam())) {
            return array_unique(array_map('trim', explode(',', $expandsStr)));
        }

        return [];
    }

    /**
     * @return integer
     */
    public function getLimit()
    {
        $limit = $this->request->get($this->requestManagerConfiguration->getLimitParam(), 0);
        return is_int($limit) ? $limit : 0;
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        $offset = $this->request->get($this->requestManagerConfiguration->getOffsetParam(), 0);
        return is_int($offset) ? $offset : 0;
    }

    /**
     * @return array
     */
    public function getSort()
    {
        if ($sortStr = $this->request->get($this->requestManagerConfiguration->getSortParam())) {
            return array_unique(array_map('trim', explode(',', $sortStr)));
        }

        return [];
    }
}
