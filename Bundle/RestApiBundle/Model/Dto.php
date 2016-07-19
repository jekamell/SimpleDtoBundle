<?php

namespace Mell\Bundle\RestApiBundle\Model;

class Dto implements DtoInterface
{
    /** @var array */
    private $data;

    /**
     * Dto constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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
        return json_encode($this->data, JSON_HEX_QUOT);
    }

    /** @return array */
    public function getRawData()
    {
        return $this->data;
    }
}
