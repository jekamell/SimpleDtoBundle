<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager;

use Doctrine\Common\Collections\Criteria;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollection;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFilterManagerInterface;

/**
 * Class ApiFiltersManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager
 */
class ApiFiltersManager implements ApiFilterManagerInterface
{
    /** @var array */
    private $aliasOperationMap = [
        ApiFilterManagerInterface::OPERATION_ALIAS_EQUAL => ApiFilter::OPERATION_EQUAL,
        ApiFilterManagerInterface::OPERATION_ALIAS_NOT_EQUAL => ApiFilter::OPERATION_NOT_EQUAL,
        ApiFilterManagerInterface::OPERATION_ALIAS_MORE_THEN => ApiFilter::OPERATION_MORE_THEN,
        ApiFilterManagerInterface::OPERATION_ALIAS_LESS_THEN => ApiFilter::OPERATION_LESS_THEN,
        ApiFilterManagerInterface::OPERATION_ALIAS_MORE_OR_EQUAL_THEN => ApiFilter::OPERATION_MORE_OR_EQUAL_THEN,
        ApiFilterManagerInterface::OPERATION_ALIAS_LESS_OR_EQUAL_THEN => ApiFilter::OPERATION_LESS_OR_EQUAL_THEN,
    ];

    /**
     * @param string $filtersStr
     * @return ApiFilter[]
     */
    public function parse($filtersStr)
    {
        $collection = new ApiFilterCollection();
        $operationsStr = implode('|', static::getOperationAliases());
        foreach (explode('|', $filtersStr) as $filter) {
            if (preg_match("/^(?<param>[a-zA-Z]+)(?<operation>$operationsStr)(?<value>.+)$/", $filter, $m)) {
                $isArray = substr($m['value'], 0, 1) === '(' && substr($m['value'], -1);
                $collection->append(
                    new ApiFilter(
                        $m['param'],
                        $this->processOperation($m['operation'], $isArray),
                        $this->processValue($m['value'], $isArray)
                    )
                );
            }
        }

        return $this->processFilters($collection);
    }

    /**
     * @return array
     */
    public static function getOperationAliases()
    {
        return [
            ApiFilterManagerInterface::OPERATION_ALIAS_EQUAL,
            ApiFilterManagerInterface::OPERATION_ALIAS_NOT_EQUAL,
            ApiFilterManagerInterface::OPERATION_ALIAS_MORE_THEN,
            ApiFilterManagerInterface::OPERATION_ALIAS_LESS_THEN,
            ApiFilterManagerInterface::OPERATION_ALIAS_LESS_OR_EQUAL_THEN,
            ApiFilterManagerInterface::OPERATION_ALIAS_MORE_OR_EQUAL_THEN,
        ];
    }

    /**
     * @param string $alias
     * @param bool $isArray
     * @return string
     */
    private function processOperation($alias, $isArray)
    {
        if (!$isArray) {
            return $this->aliasOperationMap[$alias];
        }

        return $alias === ApiFilterManagerInterface::OPERATION_ALIAS_EQUAL
            ? ApiFilter::OPERATION_IN_ARRAY
            : ApiFilter::OPERATION_NOT_IN_ARRAY;
    }

    /**
     * @param string $value
     * @param bool $isArray
     * @return mixed
     */
    private function processValue($value, $isArray)
    {
        if (!$isArray) {
            return trim($value);
        }

        $value = trim($value, '()');

        return explode(',', $value);
    }

    /**
     * @param ApiFilterCollectionInterface $filters
     * @return ApiFilterCollectionInterface
     */
    private function processFilters(ApiFilterCollectionInterface $filters)
    {
        return $filters->unique();
    }
}
