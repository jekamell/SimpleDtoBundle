<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Class ApiFilterCollection
 */
class ApiFilterCollection implements ApiFilterCollectionInterface
{
    /** @var ApiFilter[] */
    private $data;

    /**
     * ApiFilterCollection constructor.
     * @param ApiFilter[] $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->key() !== null && $this->data[$this->key()] instanceof ApiFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function unique(): ApiFilterCollectionInterface
    {
        $this->data = array_unique($this->data, SORT_REGULAR);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function append(ApiFilter $apiFilter): void
    {
        $this->data[] = $apiFilter;
    }

    /**
     * @param \Closure $closure
     * @return ApiFilterCollectionInterface
     */
    public function filter(\Closure $closure)
    {
        $this->data = array_values(array_filter($this->data, $closure));

        return $this;
    }

    /**
     * @param $param
     * @return bool
     */
    public function exists(string $param): bool
    {
        foreach ($this->data as $filter) {
            if ($filter->getParam() === $param) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $param
     * @return ApiFilter
     */
    public function getByParam(string $param): ?ApiFilter
    {
        foreach ($this->data as $filter) {
            if ($filter->getParam() === $param) {
                return $filter;
            }
        }

        return null;
    }
}
