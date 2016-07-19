<?php

namespace Mell\Bundle\RestApiBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestManager
{
    /** @var Request */
    protected $request;
    /** @var string */
    protected $fieldsParam;
    /** @var string */
    protected $expandsParam;
    /** @var string */
    protected $limitPara;
    /** @var string */
    protected $offsetParam;
    /** @var string */
    protected $sortParam;

    /**
     * RequestManager constructor.
     *
     * @param RequestStack $requestStack
     * @param string $fieldsPara
     * @param string $expandsParam
     * @param string $limitParam
     * @param string $offsetParam
     * @param string $sortParam
     */
    public function __construct(
        RequestStack $requestStack,
        $fieldsPara,
        $expandsParam,
        $limitParam,
        $offsetParam,
        $sortParam
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->fieldsParam = $fieldsPara;
        $this->expandsParam = $expandsParam;
        $this->limitParam = $limitParam;
        $this->offsetParam = $offsetParam;
        $this->sortParam = $sortParam;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        if ($fieldsStr = $this->request->get($this->fieldsParam)) {
            return array_unique(array_map('trim', explode(',', $fieldsStr)));
        }

        return [];
    }

    /**
     * @return array
     */
    public function getExpands()
    {
        if ($expandsStr = $this->request->get($this->expandsParam)) {
            return array_unique(array_map('trim', explode(',', $expandsStr)));
        }

        return [];
    }

    /**
     * @return integer
     */
    public function getLimit()
    {
        return $this->request->get($this->limitParam, 0);
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        return $this->request->get($this->offsetParam, 0);
    }

    /**
     * @return array
     */
    public function getSort()
    {
        if ($sortStr = $this->request->get($this->sortParam)) {
            return array_unique(array_map('trim', explode(',', $sortStr)));
        }

        return [];
    }
}
