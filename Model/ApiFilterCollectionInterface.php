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
     * @return void
     */
    public function append(ApiFilter $apiFilter);
}
