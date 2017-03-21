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
    protected $configurator;

    /**
     * RequestManager constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param RequestManagerConfigurator $configurator
     */
    public function setConfigurator(RequestManagerConfigurator $configurator): void
    {
        $this->configurator = $configurator;
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
        if ($fieldsStr = $this->getRequest()->get($this->configurator->getFieldsParam())) {
            return array_unique(array_map('trim', explode(',', $fieldsStr)));
        }

        return [];
    }

    /**
     * @return array
     */
    public function getExpands(): array
    {
        if ($expandsStr = $this->getRequest()->get($this->configurator->getExpandsParam())) {
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
        $limit = $this->getRequest()->get($this->configurator->getLimitParam(), 0);

        return (int)$limit;
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        $offset = $this->getRequest()->get($this->configurator->getOffsetParam(), 0);

        return (int)$offset;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        if ($sortStr = $this->getRequest()->get($this->configurator->getSortParam())) {
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
        if ($localeStr = $this->getRequest()->get($this->configurator->getLocaleParam())) {
            return $localeStr;
        }

        return $this->getRequest()->headers->get($this->configurator->getLocaleHeader());
    }

    /**
     * @return bool
     */
    public function isLinksRequired(): bool
    {
        $linksStr = $this->getRequest()->get($this->configurator->getLinksParam());

        return (bool)$linksStr;
    }

    /**
     * @return bool
     */
    public function isCountRequired(): bool
    {
        $countStr = $this->getRequest()->get($this->configurator->getCountParam());

        return (bool)$countStr;
    }

    /**
     * @return string|null
     */
    public function getApiFilters(): ?string
    {
        return $this->getRequest()->get($this->configurator->getApiFilterParam());
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
