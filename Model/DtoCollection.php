<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

class DtoCollection implements DtoCollectionInterface
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
     * {@inheritdoc}
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalData($originalData): void
    {
        if (!is_array($originalData) && !$originalData instanceof \Iterator) {
            throw new \InvalidArgumentException('First argument must be an array or implement \\Iterator interface');
        }

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
     * {@inheritdoc}
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
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
        return next($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function key(): ?int
    {
        return key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->key() !== null && $this->data[$this->key()] instanceof Dto;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): Dto
    {
        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
