<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Interface ApiFilterCollectionInterface
 */
interface ApiFilterCollectionInterface extends \Iterator, \Countable, \ArrayAccess
{
    /**
     * @return ApiFilterCollectionInterface
     */
    public function unique(): ApiFilterCollectionInterface;

    /**
     * @param ApiFilter $apiFilter
     */
    public function append(ApiFilter $apiFilter): void;

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
    public function exists(string $param): bool;

    /**
     * Get single filter by param
     * @param $param
     * @return ApiFilter
     */
    public function getByParam(string $param): ?ApiFilter;
}
