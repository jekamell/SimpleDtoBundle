<?php

namespace Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface ApiFilterManagerInterface
 * @package Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager
 */
interface ApiFilterManagerInterface
{
    const OPERATION_ALIAS_EQUAL = ':';
    const OPERATION_ALIAS_NOT_EQUAL = '!:';
    const OPERATION_ALIAS_MORE_THEN = '>:';
    const OPERATION_ALIAS_LESS_THEN = '<:';
    const OPERATION_ALIAS_LESS_OR_EQUAL_THEN = '<=:';
    const OPERATION_ALIAS_MORE_OR_EQUAL_THEN = '>=:';

    /**
     * @param string $filtersStr
     * @return ApiFilter[]
     */
    public function parse($filtersStr);
}
