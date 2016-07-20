<?php

namespace Mell\Bundle\RestApiBundle\Model;

interface DtoInterface extends \JsonSerializable
{
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY = 'array';
    const TYPE_DATE = 'date';
    const TYPE_DATE_TIME = 'datetime';

    /** @return array */
    public function getRawData();
}
