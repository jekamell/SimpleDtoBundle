<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Interface DtoInterface
 */
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
    public function getRawData(): array;

    /**
     * Reset raw data
     *
     * @param array $data
     */
    public function setRawData(array $data): void;

    /**
     * Set original data
     *
     * @param $data
     */
    public function setOriginalData($data): void;

    /**
     * Get original data
     *
     * @return mixed
     */
    public function getOriginalData();

    /**
     * @return string
     */
    public function getGroup(): string;

    /**
     * @param string $group
     */
    public function setGroup(string $group): void;
}
