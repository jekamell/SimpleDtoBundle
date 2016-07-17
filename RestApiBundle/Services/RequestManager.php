<?php

namespace Mell\RestApiBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestManager
{
    /** @var Request */
    protected $request;
    protected $fieldsParam;
    protected $expandsParam;
    protected $limitPara;
    protected $offsetParam;

    /**
     * RequestManager constructor.
     *
     * @param RequestStack $requestStack
     * @param string $fieldsPara
     * @param string $expandsParam
     * @param integer $limitParam
     * @param integer $offsetParam
     */
    public function __construct(RequestStack $requestStack, $fieldsPara, $expandsParam, $limitParam, $offsetParam)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->fieldsParam = $fieldsPara;
        $this->expandsParam = $expandsParam;
        $this->limitParam = $limitParam;
        $this->offsetParam = $offsetParam;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        if ($fieldsStr = $this->request->get($this->fieldsParam)) {
            return array_map('trim', explode(',', $fieldsStr));
        }

        return [];
    }

    /**
     * @return array
     */
    public function getExpands()
    {
        if ($fieldsStr = $this->request->get($this->expandsParam)) {
            return array_map('trim', explode(',', $fieldsStr));
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
}
