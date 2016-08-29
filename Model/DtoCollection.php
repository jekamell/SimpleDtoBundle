<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

class DtoCollection implements DtoCollectionInterface
{
    /** @var string */
    private $type;
    /** @var mixed */
    private $originalData;
    /** @var array */
    private $data;
    /** @var string */
    private $group;
    /** @var string */
    private $collectionKey;

    /**
     * DtoCollection constructor.
     * @param string $type
     * @param $originalData
     * @param string $collectionKey
     * @param null $group
     * @param DtoInterface[] $data
     */
    public function __construct($type, $originalData, $collectionKey, $group = null, array $data = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->originalData = $originalData;
        $this->collectionKey = $collectionKey;
        $this->group = $group;
    }

    /** @return array */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        $data = [];
        /** @var DtoInterface $item */
        foreach ($this->data as $item) {
            $data[] = $item->jsonSerialize();
        }

        return $this->collectionKey ? [$this->collectionKey => $data] : $data;
    }

    /**
     * @return mixed
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * @param mixed $originalData
     * @return DtoCollectionInterface
     */
    public function setOriginalData($originalData)
    {
        $this->originalData = $originalData;

        return $this;
    }

    /**
     * @param $data
     * @return DtoInterface
     */
    public function append($data)
    {
        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data[] = $data;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Return the current element
     * @return DtoInterface.
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @return DtoInterface
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * Return the key of the current element
     * @return int|null
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
        return $this->key() !== null && $this->data[$this->key()] instanceof DtoInterface;
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
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->data);
    }
}
