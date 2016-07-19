<?php

namespace Mell\Bundle\RestApiBundle\Model;

class DtoCollection implements DtoInterface
{
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

        return [$this->collectionKey => $data];
    }
}
