<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager;

use Doctrine\Common\Collections\Criteria;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollection;
use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFilterManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    /** @var array */
    private $operationSqlOperationMap = [
        ApiFilter::OPERATION_EQUAL => '=',
        ApiFilter::OPERATION_NOT_EQUAL => '!=',
        ApiFilter::OPERATION_MORE_THEN => '>',
        ApiFilter::OPERATION_LESS_THEN => '<',
        ApiFilter::OPERATION_MORE_OR_EQUAL_THEN => '>=',
        ApiFilter::OPERATION_LESS_OR_EQUAL_THEN => '<=',
        ApiFilter::OPERATION_IN_ARRAY => 'IN',
        ApiFilter::OPERATION_NOT_IN_ARRAY => 'NOT IN',
        ApiFilter::OPERATION_IS => 'IS',
        ApiFilter::OPERATION_IS_NOT => 'IS NOT',
    ];

    /**
     * @param string $filtersStr
     * @return ApiFilterCollectionInterface
     */
    public function parse($filtersStr): ApiFilterCollectionInterface
    {
        $collection = new ApiFilterCollection();
        $operationsStr = implode('|', static::getOperationAliases());
        foreach (explode('|', $filtersStr) as $filter) {
            if (preg_match("/^(?<param>[a-zA-Z]+)(?<operation>$operationsStr)(?<value>.+)$/", $filter, $m)) {
                $isArray = substr($m['value'], 0, 1) === '(' && substr($m['value'], -1);
                $value = $this->processValue($m['value'], $isArray);
                $collection->append(
                    new ApiFilter(
                        $m['param'],
                        $this->processOperation($m['operation'], $value, $isArray),
                        $value
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
     * @param string $operation
     * @return mixed
     */
    public function getSqlOperationByOperation($operation)
    {
        return $this->operationSqlOperationMap[$operation];
    }

    /**
     * @param string $operation
     * @return bool
     */
    public function isArrayOperation($operation)
    {
        return in_array($operation, [ApiFilter::OPERATION_IN_ARRAY, ApiFilter::OPERATION_NOT_IN_ARRAY]);
    }

    /**
     * @param string $alias
     * @param $value
     * @param bool $isArray
     * @return string
     */
    private function processOperation($alias, $value, $isArray)
    {
        if ($value === null) {
            if ($alias === ApiFilterManagerInterface::OPERATION_ALIAS_EQUAL) {
                return ApiFilter::OPERATION_IS;
            } elseif (ApiFilterManagerInterface::OPERATION_ALIAS_NOT_EQUAL) {
                return ApiFilter::OPERATION_IS_NOT;
            } else {
                throw new BadRequestHttpException('Mailformed api filters format');
            }
        }

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
            return $value === 'null' ? null : trim($value);
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
