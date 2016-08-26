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
    private $collectionKey;

    /**
     * DtoCollection constructor.
     * @param string $type
     * @param DtoInterface[] $data
     * @param $originalData
     * @param string $collectionKey
     */
    public function __construct($type, array $data, $originalData, $collectionKey)
    {
        $this->type = $type;
        $this->data = $data;
        $this->originalData = $originalData;
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
}
