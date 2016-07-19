<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Exceptions\DtoException;

class DtoValidator
{
    /**
     * @param array $config Parsed dto config
     * @param \stdClass $object Object to serialize
     * @param string $type
     */
    public function validateDto(array $config, $object, $type)
    {
        $this->validateDtoExist($config, $type);
        $this->validateDtoFields($config[$type], $object, $type);
    }

    /**
     * @param array $config
     * @param array $type
     * @throws DtoException
     */
    protected function validateDtoExist(array $config, $type)
    {
        if (!isset($config[$type])) {
            throw new DtoException(sprintf('Dto definition not found: %s', $type));
        }
    }

    /**
     * @param array $config
     * @param $object
     * @param $type
     * @throws DtoException
     */
    protected function validateDtoFields(array $config, $object, $type)
    {
        if (empty($config['fields'])) {
            throw new DtoException(sprintf('Fields definition not found: %s', $type));
        }

        foreach ($config['fields'] as $field => $options) {
            $getter = isset($options['getter']) ? $options['getter'] : $this->getFieldGetter($field);
            if (!method_exists($object, $getter)) {
                throw new DtoException(sprintf('%s: Method not found: %s', $type, $getter));
            }
        }
    }

    /**
     * @param string $field
     * @return string
     */
    private function getFieldGetter($field)
    {
        return 'get' . ucfirst($field);
    }
}
