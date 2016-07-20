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
     * @param $data
     * @param string $dtoType
     * @param array $allowedExpands
     * @return DtoInterface
     */
    public function createDto($data, $dtoType, array $allowedExpands = [])
    {
        $dtoConfig = $this->dtoHelper->getDtoConfig();
        $this->validateDto($dtoConfig, $dtoType, $data);

        $requestedFields = $this->requestManager->getFields();
        $requestedExpands = $this->requestManager->getExpands();

        $dtoData = [];
        foreach ($dtoConfig[$dtoType]['fields'] as $field => $options) {
            $this->processFields($data, $dtoData, $field, $options, $requestedFields);
        }

        $this->processExpands(
            $data,
            $dtoData,
            $dtoConfig[$dtoType],
            $requestedExpands,
            $allowedExpands
        );

        return new Dto($dtoData);
    }

    /**
     * @param array $data
     * @param $dtoType
     * @param array $allowedExpands
     * @return DtoInterface
     */
    public function createDtoCollection(array $data, $dtoType, array $allowedExpands = [])
    {
        $collection = [];
        foreach ($data as $item) {
            $collection[] = $this->createDto($item, $dtoType, $allowedExpands);
        }

        return new DtoCollection($collection, $this->configurator->getCollectionKey());
    }

    /**
     * @param $data
     * @param array $dtoData
     * @param string $field
     * @param array $options
     * @param array $requestedFields
     */
    protected function processFields($data, array &$dtoData, $field, array $options, array $requestedFields)
    {
        // process _fields param
        if (!empty($requestedFields) && !in_array($field, $requestedFields)) {
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
    protected function validateDto($dtoConfig, $dtoType, $object)
    {
        $this->dtoValidator->validateDto($dtoConfig, $object, $dtoType);
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
    protected function castValueType($type, $value)
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
                $value = $value->format($this->configurator->getFormatDate());
                break;
            case DtoInterface::TYPE_DATE_TIME:
                if (!$value instanceof \DateTime) {
                    $value = new \DateTime($value);
                }
                $value = $value->format($this->configurator->getFormatDateTime());
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
