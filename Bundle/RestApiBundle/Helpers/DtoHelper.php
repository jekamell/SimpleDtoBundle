<?php

namespace Mell\Bundle\RestApiBundle\Helpers;

class DtoHelper
{
    /**
     * @param $field
     * @return string
     */
    public function getFieldGetter($field)
    {
        return 'get' . ucfirst($field);
    }
}
