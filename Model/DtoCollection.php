<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

class DtoCollection implements \Iterator, \Countable, \ArrayAccess, \JsonSerializable
{
    /** @var array */
    private $originalData;
    /** @var array */
    private $data;
    /** @var string */
    private $group;
    /** @var string */
    private $collectionKey;
    /** @var integer */
    private $count;

    /**
     * DtoCollection constructor.
     * @param array $originalData
     * @param string $collectionKey
     * @param string $group
     * @param Dto[] $data
     * @param int|null $count
     */
    public function __construct(
        array $originalData,
            string $collectionKey,
            string $group,
            array $data = [],
            ?int $count = null
    ) {
        $this->data = $data;
        $this->originalData = $originalData;
        $this->collectionKey = $collectionKey;
        $this->group = $group;
        $this->count = $count;
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setRawData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize(): array
    {
        $data = [];
        /** @var DtoInterface $item */
        foreach ($this->data as $item) {
            $data[] = $item->jsonSerialize();
        }

        if (!$this->collectionKey) {
            return $data;
        }

        if ($this->count !== null) {
            $result['_count'] = $this->count;
        }

        $result[$this->collectionKey] = $data;

        return $result;
    }

    /**
     * @return array
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    /**
     * @param mixed $originalData
     */
    public function setOriginalData(array $originalData): void
    {
        $this->originalData = $originalData;
    }

    /**
     * @param $data
     */
    public function append($data): void
    {
        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data[] = $data;
        }
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * Return the current element
     * @return Dto
     */
    public function current(): Dto
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @return Dto
     */
    public function next(): Dto
    {
        return next($this->data);
    }

    /**
     * Return the key of the current element
     * @return int|null
     */
    public function key(): ?int
    {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     * @return boolean The return value will be casted to boolean and then evaluated.
     */
    public function valid(): bool
    {
        return $this->key() !== null && $this->data[$this->key()] instanceof DtoInterface;
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Whether a offset exists
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     * @param mixed $offset
     * @return Dto
     */
    public function offsetGet($offset): Dto
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
