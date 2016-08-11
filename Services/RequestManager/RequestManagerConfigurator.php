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
    /** @var string */
    protected $localeHeader;
    /** @var string */
    protected $localeParam;

    /**
     * RequestManagerConfiguration constructor.
     * @param string $fieldsParam
     * @param string $expandsParam
     * @param string $limitParam
     * @param string $offsetParam
     * @param string $sortParam
     * @param string $localeHeader
     * @param string $localeParam
     */
    public function __construct(
        $fieldsParam,
        $expandsParam,
        $limitParam,
        $offsetParam,
        $sortParam,
        $localeHeader,
        $localeParam
    ) {
        $this->fieldsParam = $fieldsParam;
        $this->expandsParam = $expandsParam;
        $this->limitParam = $limitParam;
        $this->offsetParam = $offsetParam;
        $this->sortParam = $sortParam;
        $this->localeHeader = $localeHeader;
        $this->localeParam = $localeParam;
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

    /**
     * @return string
     */
    public function getLocaleHeader()
    {
        return $this->localeHeader;
    }

    /**
     * @param string $localeHeader
     */
    public function setLocaleHeader($localeHeader)
    {
        $this->localeHeader = $localeHeader;
    }

    /**
     * @return string
     */
    public function getLocaleParam()
    {
        return $this->localeParam;
    }

    /**
     * @param string $localeParam
     */
    public function setLocaleParam($localeParam)
    {
        $this->localeParam = $localeParam;
    }
}
