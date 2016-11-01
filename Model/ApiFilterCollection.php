<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Class ApiFilterCollection
 * @package Mell\Bundle\SimpleDtoBundle\Model
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
     * Return the current element
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     * @return boolean The return value will be casted to boolean and then evaluated.
     */
    public function valid()
    {
        return $this->key() !== null && $this->data[$this->key()] instanceof ApiFilter;
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * Whether a offset exists
     * @param int $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     * @param int $offset <p>
     * @return ApiFilter
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     * @param int $offset
     * @param ApiFilter $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     * @param int $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return ApiFilterCollectionInterface
     */
    public function unique()
    {
        $this->data = array_unique($this->data, SORT_REGULAR);

        return $this;
    }

    /**
     * @param ApiFilter $apiFilter
     * @return ApiFilterCollectionInterface
     */
    public function append(ApiFilter $apiFilter)
    {
        $this->data[] = $apiFilter;

        return $this;
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
    public function exists($param)
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
    public function getByParam($param)
    {
        foreach ($this->data as $filter) {
            if ($filter->getParam() === $param) {
                return $filter;
            }
        }

        return null;
    }
}
