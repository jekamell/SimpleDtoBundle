<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Helpers\DtoHelper;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Model\DtoCollection;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Mell\Bundle\RestApiBundle\Model\DtoManagerConfigurator;
use Mell\Bundle\RestApiBundle\Services\RequestManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class DtoManager
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var DtoValidator */
    protected $dtoValidator;
    /** @var DtoHelper */
    protected $dtoHelper;
    /** @var Yaml */
    protected $yaml;
    /** @var  FileLocator */
    protected $fileLocator;
    /** @var DtoManagerConfigurator */
    protected $configurator;
    /** @var array */
    private $dtoConfig;

    /**
     * DtoManager constructor.
     * @param RequestManager $requestManager
     * @param DtoValidator $dtoValidator
     * @param DtoHelper $dtoHelper
     * @param FileLocator $fileLocator
     * @param DtoManagerConfigurator $configurator
     */
    public function __construct(
        RequestManager $requestManager,
        DtoValidator $dtoValidator,
        DtoHelper $dtoHelper,
        FileLocator $fileLocator,
        DtoManagerConfigurator $configurator
    ) {
        $this->requestManager = $requestManager;
        $this->dtoValidator = $dtoValidator;
        $this->dtoHelper = $dtoHelper;
        $this->fileLocator = $fileLocator;
        $this->configurator = $configurator;
    }

    /**
     * @param $data
     * @param string $dtoType
     * @return Dto
     */
    public function createDto($data, $dtoType)
    {
        $dtoConfig = $this->getDtoConfig();
        $this->validateDto($dtoConfig, $dtoType, $data);

        $requestedFields = $this->requestManager->getFields();

        $dtoData = [];
        foreach ($dtoConfig[$dtoType]['fields'] as $field => $options) {
            $this->processFields($data, $dtoData, $field, $options, $requestedFields);
        }

        return new Dto($dtoData);
    }

    /**
     * @param array $data
     * @param $dtoType
     * @return DtoCollection
     */
    public function createDtoCollection(array $data, $dtoType)
    {
        $collection = [];
        foreach ($data as $item) {
            $collection[] = $this->createDto($item, $dtoType);
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
     * @return array
     */
    protected function getDtoConfig()
    {
        // TODO: caching
        if ($this->dtoConfig === null) {
            $absolutePath = $this->fileLocator->locate($this->configurator->getConfigPath());
            $this->dtoConfig = Yaml::parse(file_get_contents($absolutePath));
        }

        return $this->dtoConfig;
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
