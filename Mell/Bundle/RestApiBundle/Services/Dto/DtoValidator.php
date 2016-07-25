<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Exceptions\DtoException;
use Mell\Bundle\RestApiBundle\Helpers\DtoHelper;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * TODO: description
     *
     * @param DtoInterface $dto
     * @param $config
     * @param $dtoType
     */
    public function validateDto(DtoInterface $dto, $config, $dtoType)
    {
        $this->validateDtoExist($config, $dtoType);
        $this->validateDtoFields($dto, $config[$dtoType], $dtoType);

    }

    /**
     * @param array $config Parsed dto config
     * @param \stdClass $object Object to serialize
     * @param string $type
     */
    public function validateDtoConfig(array $config, $object, $type)
    {
        $this->validateDtoExist($config, $type);
        $this->validateDtoConfigFields($config[$type], $object, $type);
    }

    /**
     * @param array $config
     * @param array $expands
     * @throws DtoException
     */
    public function validateExpands(array $config, array $expands)
    {
        foreach ($expands as $expand) {
            if (!array_key_exists($expand, $config)) {
                throw new BadRequestHttpException(sprintf('Invalid expands required: %s', $expand));
            }
            if (!isset($config[$expand]['type'])) {
                throw new DtoException(sprintf('Expand type should be defined: %s', $expand));
            }
        }
    }

    /**
     * TODO: description
     *
     * @param DtoInterface $dto
     * @param array $config
     * @param $type
     */
    protected function validateDtoFields(DtoInterface $dto, array $config, $type)
    {
        $fieldsConfig = $config['fields'];
        foreach ($dto->getRawData() as $field => $value) {
            if (!empty($fieldsConfig[$field]['readonly'])) {
                throw new BadRequestHttpException(sprintf('%s: Field "%s" is readonly', $type, $field));
            }
        }
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
    protected function validateDtoConfigFields(array $config, $object, $type)
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
