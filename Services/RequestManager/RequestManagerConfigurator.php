<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\RequestManager;

class RequestManagerConfigurator
{
    /** @var string */
    protected $fieldsParam;
    /** @var string */
    protected $expandsParam;
    /** @var string */
    protected $limitParam;
    /** @var string */
    protected $offsetParam;
    /** @var string */
    protected $sortParam;

    /**
     * RequestManagerConfiguration constructor.
     * @param string $fieldsParam
     * @param string $expandsParam
     * @param string $limitParam
     * @param string $offsetParam
     * @param string $sortParam
     */
    public function __construct($fieldsParam, $expandsParam, $limitParam, $offsetParam, $sortParam)
    {
        $this->fieldsParam = $fieldsParam;
        $this->expandsParam = $expandsParam;
        $this->limitParam = $limitParam;
        $this->offsetParam = $offsetParam;
        $this->sortParam = $sortParam;
    }

    /**
     * @return string
     */
    public function getFieldsParam()
    {
        return $this->fieldsParam;
    }

    /**
     * @return string
     */
    public function getExpandsParam()
    {
        return $this->expandsParam;
    }

    /**
     * @return string
     */
    public function getLimitParam()
    {
        return $this->limitParam;
    }

    /**
     * @return string
     */
    public function getOffsetParam()
    {
        return $this->offsetParam;
    }

    /**
     * @return string
     */
    public function getSortParam()
    {
        return $this->sortParam;
    }
}
