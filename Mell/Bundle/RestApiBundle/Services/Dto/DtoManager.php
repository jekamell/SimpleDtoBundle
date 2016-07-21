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
     * @param array $allowedExpands
     * @return DtoInterface
     */
    public function createDto($entity, $dtoType, $group, array $allowedExpands = [])
    {
        $dtoConfig = $this->dtoHelper->getDtoConfig();
        $this->validateDtoConfig($dtoConfig, $dtoType, $entity);

        $requestedFields = $this->requestManager->getFields();
        $requestedExpands = $this->requestManager->getExpands();

        $dtoData = [];
        foreach ($dtoConfig[$dtoType]['fields'] as $field => $options) {
            $this->processField($entity, $dtoData, $group, $field, $options, $requestedFields);
        }

        $this->processExpands(
            $entity,
            $dtoData,
            $dtoConfig[$dtoType],
            $requestedExpands,
            $allowedExpands
        );

        return new Dto($dtoData);
    }

    /**
     * @param array $collection
     * @param $dtoType
     * @param array $allowedExpands
     * @return DtoInterface
     */
    public function createDtoCollection(array $collection, $dtoType, $group, array $allowedExpands = [])
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->createDto($item, $dtoType, $group, $allowedExpands);
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
     * @param $data
     * @param array $dtoData
     * @param $group
     * @param string $field
     * @param array $options
     * @param array $requestedFields
     */
    protected function processField($data, array &$dtoData, $group,  $field, array $options, array $requestedFields)
    {
        if (!empty($requestedFields) && !in_array($field, $requestedFields)) {
            return;
        }
        if (!empty($options['groups']) && !in_array($group, $options['groups'])) {
            return;
        }

        $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
        $value = call_user_func([$data, $getter]);
        $dtoData[$field] = $this->castValueType($options['type'], $value);
    }

    /**
     * @param $data
     * @param array $dtoData
     * @param $dtoConfig
     * @param array $requestedExpands
     * @param array $allowedExpands
     */
    protected function processExpands(
        $data,
        array &$dtoData,
        $dtoConfig,
        array $requestedExpands,
        array $allowedExpands
    ) {
        // process _expands param
        if (empty($requestedExpands) || empty($dtoConfig['expands'])) {
            return;
        }

        $expandsConfig = $dtoConfig['expands'];
        $this->validateExpands($expandsConfig, $requestedExpands);
        foreach (array_intersect($requestedExpands, $allowedExpands) as $requestedExpand) {
            $expandConfig = $expandsConfig[$requestedExpand];
            $expandGetter = !empty($expandConfig['getter'])
                ? $expandConfig['getter']
                : $this->dtoHelper->getFieldGetter($requestedExpand);
            $expandObject = call_user_func([$data, $expandGetter]);
            $dtoData['_expands'][$requestedExpand][] = $this->createDto($expandObject, $expandConfig['type'], []);
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
     * @param array $expandsConfig
     * @param array $requestedExpands
     */
    protected function validateExpands(array $expandsConfig, array $requestedExpands)
    {
        return $this->dtoValidator->validateExpands($expandsConfig, $requestedExpands);
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
