<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Exceptions\DtoException;
use Mell\Bundle\RestApiBundle\Helpers\DtoHelper;
use Mell\Bundle\RestApiBundle\Model\Dto;

class DtoValidator
{
    /** @var DtoHelper */
    protected $dtoHelper;

    /**
     * DtoValidator constructor.
     * @param DtoHelper $dtoHelper
     */
    public function __construct(DtoHelper $dtoHelper)
    {
        $this->dtoHelper = $dtoHelper;
    }

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
     * @param string $type
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
            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            if (!method_exists($object, $getter)) {
                throw new DtoException(sprintf('%s: Method not found: %s', $type, $getter));
            }
        }
    }

    /**
     * @param array $config
     * @param string $type
     * @throws DtoException
     */
    protected function validateDtoTypes(array $config, $type)
    {
        foreach ($config['fields'] as $field => $options) {
            if (empty($options['type'])) {
                throw new DtoException('%s: Field type should be defined: %s', $type, $field);
            }
            if (!in_array($options['type'], Dto::getAvailableTypes())) {
                throw new DtoException(
                    sprintf(
                        '%s: Unsupported field type: %s. User one of: %s',
                        $type, $options['type'],
                        implode(',', Dto::getAvailableTypes())
                    )
                );
            }
        }
    }
}
