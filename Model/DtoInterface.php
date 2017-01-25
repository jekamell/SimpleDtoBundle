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
     * Reset raw data
     *
     * @param array $data
     * @return $this
     */
    public function setRawData(array $data);

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
     * @return mixed
     */
    public function getOriginalData();

    /**
     * @param array|DtoInterface $data
     * @return $this
     */
    public function append($data);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string|null
     */
    public function getGroup();

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup($group);
}
