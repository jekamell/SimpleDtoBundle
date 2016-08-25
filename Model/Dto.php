<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

class Dto implements DtoInterface
{
    /** @var mixed */
    private $originData;

    /** @var array */
    private $data;

    /**
     * Dto constructor.
     * @param array $data
     * @param object $originalData
     */
    public function __construct(array $data = [], $originalData = null)
    {
        $this->data = $data;
        $this->originData = $originalData;
    }

    /**
     * @return array
     */
    public static function getAvailableTypes()
    {
        return [
            DtoInterface::TYPE_INTEGER,
            DtoInterface::TYPE_FLOAT,
            DtoInterface::TYPE_STRING,
            DtoInterface::TYPE_BOOLEAN,
            DtoInterface::TYPE_ARRAY,
            DtoInterface::TYPE_DATE,
            DtoInterface::TYPE_DATE_TIME,
        ];
    }

    /**
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->data;
    }

    /** @return array */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * Set dto source
     *
     * @param $data
     * @return DtoInterface
     */
    public function setOriginalData($data)
    {
        $this->originData = $data;

        return $this;
    }

    /**
     * get dto source
     *
     * @return mixed
     */
    public function getOriginalData()
    {
        return $this->originData;
    }

    /**
     * @param array $data
     * @return DtoInterface
     */
    public function append(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }
}
