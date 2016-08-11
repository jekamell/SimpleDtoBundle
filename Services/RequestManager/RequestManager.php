<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\RequestManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestManager
{
    const SORT_DIRECTION_DEFAULT = self::SORT_DIRECTION_ACS;
    const SORT_DIRECTION_ACS = 'asc';
    const SORT_DIRECTION_DESC = 'desc';

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
    public static function getAllowedSortDirections()
    {
        return [self::SORT_DIRECTION_ACS, self::SORT_DIRECTION_DESC];
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
     * @return string
     */
    public function getLocale()
    {
        if ($localeStr = $this->request->get($this->requestManagerConfiguration->getLocaleParam())) {
            return $localeStr;
        }

        return $this->request->headers->get($this->requestManagerConfiguration->getLocaleHeader());
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
