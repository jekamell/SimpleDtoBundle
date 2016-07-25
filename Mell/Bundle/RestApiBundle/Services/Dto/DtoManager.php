<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Helpers\DtoHelper;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Model\DtoCollection;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Mell\Bundle\RestApiBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\RestApiBundle\Services\RequestManager;

class DtoManager
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var DtoValidator */
    protected $dtoValidator;
    /** @var DtoHelper */
    protected $dtoHelper;
    /** @var DtoManagerConfigurator */
    protected $configurator;

    /**
     * DtoManager constructor.
     * @param RequestManager $requestManager
     * @param DtoValidator $dtoValidator
     * @param DtoHelper $dtoHelper
     * @param DtoManagerConfigurator $configurator
     */
    public function __construct(
        RequestManager $requestManager,
        DtoValidator $dtoValidator,
        DtoHelper $dtoHelper,
        DtoManagerConfigurator $configurator
    ) {
        $this->requestManager = $requestManager;
        $this->dtoValidator = $dtoValidator;
        $this->dtoHelper = $dtoHelper;
        $this->configurator = $configurator;
    }

    /**
     * Convert entity to dto
     *
     * @param $entity
     * @param string $dtoType
     * @param string $group
     * @param array $fields
     * @param array $expands
     * @return DtoInterface
     */
    public function createDto($entity, $dtoType, $group, array $fields = [], array $expands = [])
    {
        $dtoConfig = $this->dtoHelper->getDtoConfig();
        $this->validateDtoConfig($dtoConfig, $dtoType, $entity);

        $dtoData = [];
        $this->processFields($entity, $dtoData, $fields, $dtoConfig[$dtoType]['fields'], $group);
        $this->processExpands(
            $entity,
            $dtoData,
            $expands,
            isset($dtoConfig[$dtoType]['expands']) ? $dtoConfig[$dtoType]['expands'] : [],
            $group
        );

        return new Dto($dtoData);
    }

    /**
     * @param array $collection
     * @param $dtoType
     * @param $group
     * @param array $fields
     * @param array $expands
     * @return DtoInterface
     */
    public function createDtoCollection(array $collection, $dtoType, $group, array $fields = [], array $expands = [])
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->createDto($item, $dtoType, $group, $fields, $expands);
        }

        return new DtoCollection($data, $this->configurator->getCollectionKey());
    }

    /**
     * @param $entity
     * @param DtoInterface $dto
     * @param string $dtoType
     * @param string|null $group
     * @return mixed
     */
    public function createEntityFromDto($entity, DtoInterface $dto, $dtoType, $group)
    {
        $dtoConfig = $this->dtoHelper->getDtoConfig();
        $this->validateDto($dto, $dtoConfig, $dtoType);

        $fieldsConfig = $dtoConfig[$dtoType]['fields'];
        foreach ($dto->getRawData() as $property => $value) {
            if (!empty($fieldsConfig[$property]['readonly'])) {
                continue;
            }
            if (isset($fieldsConfig[$property]['groups']) && !in_array($group, $fieldsConfig[$property]['groups'])) {
                continue;
            }
            $value = $this->castValueType($fieldsConfig[$property]['type'], $value, false);
            $setter = isset($fieldsConfig[$property]['setter'])
                ? $fieldsConfig[$property]['setter']
                :  $this->dtoHelper->getFieldSetter($property);

            call_user_func([$entity, $setter], $value);
        }

        return $entity;
    }

    /**
     * @param $entity
     * @param array $dtoData Predefined dto data
     * @param array $fields Required fields
     * @param array $config Fields configuration
     * @param string $group Dto group
     */
    protected function processFields($entity,array &$dtoData, array $fields, array $config, $group) {
        /** @var array $options */
        foreach ($config as $field => $options) {
            // field was not required (@see dtoManager::getRequiredFields)
            if (!empty($fields) && !in_array($field, $fields)) {
                continue;
            }
            // field is not allowed for specified group
            if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
                continue;
            }

            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $value = call_user_func([$entity, $getter]);
            $dtoData[$field] = $this->castValueType($options['type'], $value);
        }
    }

    /**
     * @param $entity
     * @param array $dtoData Predefined dto data
     * @param array $expands Required expands
     * @param array $config Expands configuration
     * @param string $group Dto group
     */
    protected function processExpands($entity, array &$dtoData, array $expands, array $config, $group)
    {
        if (empty($config)) {
            return;
        }
        $this->validateExpands($config, $expands);
        foreach ($expands as $expand) {
            $expandConfig = $config[$expand];
            $expandGetter = !empty($expandConfig['getter'])
                ? $expandConfig['getter']
                : $this->dtoHelper->getFieldGetter($expand);
            $expandObject = call_user_func([$entity, $expandGetter]);

            if ($expandObject) {
                continue;
            }
            $dtoData['_expands'][$expand][] = $this->createDto($expandObject, $expandConfig['type'], $group, []);
        }
    }

    /**
     * @param array $dtoConfig
     * @param string $dtoType
     * @param $object
     */
    protected function validateDtoConfig($dtoConfig, $dtoType, $object)
    {
        $this->dtoValidator->validateDtoConfig($dtoConfig, $object, $dtoType);
    }

    /**
     * @param DtoInterface $dto
     * @param array $config
     * @param string $dtoType
     */
    protected function validateDto(DtoInterface $dto, $config, $dtoType)
    {
        $this->dtoValidator->validateDto($dto, $config, $dtoType);
    }

    /**
     * @param array $config
     * @param array $expands
     */
    protected function validateExpands(array $config, array $expands)
    {
        return $this->dtoValidator->validateExpands($config, $expands);
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    protected function castValueType($type, $value, $raw = true)
    {
        switch ($type) {
            case DtoInterface::TYPE_INTEGER:
                $value = intval($value);
                break;
            case DtoInterface::TYPE_FLOAT:
                $value = floatval($value);
                break;
            case DtoInterface::TYPE_STRING:
                $value = (string)$value;
                break;
            case DtoInterface::TYPE_BOOLEAN:
                $value = boolval($value);
                break;
            case DtoInterface::TYPE_ARRAY:
                $value = (array)$value;
                break;
            case DtoInterface::TYPE_DATE:
                if (!$value instanceof \DateTime) {
                    $value = new \DateTime($value);
                }
                if ($raw) {
                    $value = $value->format($this->configurator->getFormatDate());
                }
                break;
            case DtoInterface::TYPE_DATE_TIME:
                if (!$value instanceof \DateTime) {
                    $value = new \DateTime($value);
                }
                if ($raw) {
                    $value = $value->format($this->configurator->getFormatDateTime());
                }
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
