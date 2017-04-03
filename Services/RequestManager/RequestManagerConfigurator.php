<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\RequestManager;

/**
 * Class RequestManagerConfigurator
 */
class RequestManagerConfigurator
{
    /** @var string */
    protected $fieldsParam;
    /** @var string */
    protected $expandsParam;
    /** @var string */
    protected $limitParam;
    /** @var string */
    protected $linksParam;
    /** @var string */
    protected $offsetParam;
    /** @var string */
    protected $sortParam;
    /** @var string */
    protected $localeHeader;
    /** @var string */
    protected $localeParam;
    /** @var string */
    protected $apiFilterParam;
    /** @var string */
    protected $countParam;

    /**
     * RequestManagerConfigurator constructor.
     * @param string $fieldsParam
     * @param string $expandsParam
     * @param string $limitParam
     * @param string $offsetParam
     * @param string $sortParam
     * @param string $localeParam
     * @param string $localeHeader
     * @param string $linksParam
     * @param string $apiFilterParam
     * @param string $countParam
     */
    public function __construct(
        string $fieldsParam,
        string $expandsParam,
        string $limitParam,
        string $offsetParam,
        string $sortParam,
        string $localeParam,
        string $localeHeader,
        string $linksParam,
        string $apiFilterParam,
        string $countParam
    ) {
        $this->fieldsParam = $fieldsParam;
        $this->expandsParam = $expandsParam;
        $this->limitParam = $limitParam;
        $this->offsetParam = $offsetParam;
        $this->sortParam = $sortParam;
        $this->localeHeader = $localeHeader;
        $this->localeParam = $localeParam;
        $this->linksParam = $linksParam;
        $this->apiFilterParam = $apiFilterParam;
        $this->countParam = $countParam;
    }

    /**
     * @return string
     */
    public function getFieldsParam(): string
    {
        return $this->fieldsParam;
    }

    /**
     * @return string
     */
    public function getExpandsParam(): string
    {
        return $this->expandsParam;
    }

    /**
     * @return string
     */
    public function getLimitParam(): string
    {
        return $this->limitParam;
    }

    /**
     * @return string
     */
    public function getOffsetParam(): string
    {
        return $this->offsetParam;
    }

    /**
     * @return string
     */
    public function getSortParam(): string
    {
        return $this->sortParam;
    }

    /**
     * @return string
     */
    public function getLocaleHeader(): string
    {
        return $this->localeHeader;
    }

    /**
     * @param string $localeHeader
     */
    public function setLocaleHeader(string $localeHeader): void
    {
        $this->localeHeader = $localeHeader;
    }

    /**
     * @return string
     */
    public function getLocaleParam(): string
    {
        return $this->localeParam;
    }

    /**
     * @param string $localeParam
     */
    public function setLocaleParam(string $localeParam): void
    {
        $this->localeParam = $localeParam;
    }

    /**
     * @return string
     */
    public function getLinksParam(): string
    {
        return $this->linksParam;
    }

    /**
     * @param string $linksParam
     */
    public function setLinksParam(string $linksParam): void
    {
        $this->linksParam = $linksParam;
    }

    /**
     * @return string
     */
    public function getApiFilterParam(): string
    {
        return $this->apiFilterParam;
    }

    /**
     * @param string $apiFilterParam
     */
    public function setApiFilterParam(string $apiFilterParam): void
    {
        $this->apiFilterParam = $apiFilterParam;
    }

    /**
     * @return string
     */
    public function getCountParam(): string
    {
        return $this->countParam;
    }

    /**
     * @param string $countParam
     */
    public function setCountParam(string $countParam): void
    {
        $this->countParam = $countParam;
    }

    /**
     * @param RequestManager $requestManager
     */
    public function configure(RequestManager $requestManager): void
    {
        $requestManager->setConfigurator($this);
    }
}
