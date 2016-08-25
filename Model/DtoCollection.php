<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

class DtoCollection implements DtoCollectionInterface
{
    /** @var mixed */
    private $originalData;
    /** @var array */
    protected $data;
    /** @var string */
    protected $collectionKey;

    /**
     * DtoCollection constructor.
     * @param DtoInterface[] $data
     * @param string $collectionKey
     */
    public function __construct(array $data, $collectionKey)
    {
        $this->data = $data;
        $this->collectionKey = $collectionKey;
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
     * @param array $data
     * @return DtoInterface
     */
    public function append(array $data)
    {
        $this->data[] = $data;

        return $this;
    }
}
