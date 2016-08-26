<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

interface DtoInterface extends \JsonSerializable
{
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY = 'array';
    const TYPE_DATE = 'date';
    const TYPE_DATE_TIME = 'datetime';

    const DTO_GROUP_CREATE = 'create';
    const DTO_GROUP_READ = 'read';
    const DTO_GROUP_UPDATE = 'update';
    const DTO_GROUP_DELETE = 'delete';
    const DTO_GROUP_LIST = 'list';

    /**
     * Return plain dto data
     *
     * @return array
     */
    public function getRawData();

    /**
     * Set dto source
     *
     * @param $data
     * @return $this
     */
    public function setOriginalData($data);

    /**
     * get dto source
     *
     * @return $this
     */
    public function getOriginalData();

    /**
     * @param array $data
     * @return $this
     */
    public function append(array $data);

    /**
     * @return $this
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);
}
