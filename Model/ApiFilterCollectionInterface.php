<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Interface ApiFilterCollectionInterface
 * @package Mell\Bundle\SimpleDtoBundle\Model
 */
interface ApiFilterCollectionInterface extends \Iterator, \Countable, \ArrayAccess
{
    /**
     * @return ApiFilterCollectionInterface
     */
    public function unique();

    /**
     * @param ApiFilter $apiFilter
     * @return ApiFilterCollectionInterface
     */
    public function append(ApiFilter $apiFilter);

    /**
     * @param \Closure $closure
     * @return ApiFilterCollectionInterface
     */
    public function filter(\Closure $closure);

    /**
     * Check if filter by given param exists
     * @param $param
     * @return bool
     */
    public function exists($param);
}
