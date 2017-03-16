<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\RequestManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class RequestManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\RequestManager
 */
class RequestManager
{
    const SORT_DIRECTION_DEFAULT = self::SORT_DIRECTION_ACS;
    const SORT_DIRECTION_ACS = 'asc';
    const SORT_DIRECTION_DESC = 'desc';

    /** @var RequestStack */
    protected $requestStack;
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
        $this->requestStack = $requestStack;
        $this->requestManagerConfiguration = $requestManagerConfiguration;
    }

    /**
     * @return array
     */
    public static function getAllowedSortDirections(): array
    {
        return [self::SORT_DIRECTION_ACS, self::SORT_DIRECTION_DESC];
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        if ($fieldsStr = $this->getRequest()->get($this->requestManagerConfiguration->getFieldsParam())) {
            return array_unique(array_map('trim', explode(',', $fieldsStr)));
        }

        return [];
    }

    /**
     * @return array
     */
    public function getExpands(): array
    {
        if ($expandsStr = $this->getRequest()->get($this->requestManagerConfiguration->getExpandsParam())) {
            preg_match_all(
                '~(?P<expand>\w+)(?P<fields>\(.*?\))?~x',
                str_replace(' ', '', $expandsStr),
                $result,
                PREG_SET_ORDER
            );
            $expands = [];
            foreach ($result as $item) {
                $expands[$item['expand']] = isset($item['fields']) ? explode(',', trim($item['fields'], '()')) : [];
            }

            return $expands;
        }

        return [];
    }

    /**
     * @return integer
     */
    public function getLimit(): int
    {
        $limit = $this->getRequest()->get($this->requestManagerConfiguration->getLimitParam(), 0);

        return (int)$limit;
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        $offset = $this->getRequest()->get($this->requestManagerConfiguration->getOffsetParam(), 0);

        return (int)$offset;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        if ($sortStr = $this->getRequest()->get($this->requestManagerConfiguration->getSortParam())) {
            $sortParts = array_unique(array_map('trim', explode(',', $sortStr)));
            $sort = [];
            foreach ($sortParts as $sortItem) {
                $itemParts = explode('.', $sortItem);
                if (count($itemParts) === 1) {
                    $sort[$itemParts[0]] = self::SORT_DIRECTION_DEFAULT;
                } else {
                    $this->validateDirection($itemParts[1]);
                    $sort[$itemParts[0]] = $itemParts[1];
                }
            }

            return $sort;
        }

        return [];
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        if ($localeStr = $this->getRequest()->get($this->requestManagerConfiguration->getLocaleParam())) {
            return $localeStr;
        }

        return $this->getRequest()->headers->get($this->requestManagerConfiguration->getLocaleHeader());
    }

    /**
     * @return bool
     */
    public function isLinksRequired(): bool
    {
        $linksStr = $this->getRequest()->get($this->requestManagerConfiguration->getLinksParam());

        return (bool)$linksStr;
    }

    /**
     * @return bool
     */
    public function isCountRequired(): bool
    {
        $countStr = $this->getRequest()->get($this->requestManagerConfiguration->getCountParam());

        return (bool)$countStr;
    }

    /**
     * @return string|null
     */
    public function getApiFilters(): ?string
    {
        return $this->getRequest()->get($this->requestManagerConfiguration->getApiFilterParam());
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param $direction
     */
    private function validateDirection($direction)
    {
        if (!in_array($direction, static::getAllowedSortDirections())) {
            throw new BadRequestHttpException(
                sprintf(
                    'Direction should be one of: %s. "%s" given',
                    implode(',', static::getAllowedSortDirections()),
                    $direction
                )
            );
        }
    }
}
