<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Helpers\DtoHelper;
use Mell\Bundle\RestApiBundle\Model\Dto;
use Mell\Bundle\RestApiBundle\Model\DtoInterface;
use Mell\Bundle\RestApiBundle\Services\RequestManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Validator\Constraints\DateTime;
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
    /** @var string */
    protected $configPath;
    /** @var array */
    protected $dtoConfig;
    /** @var string */
    protected $dateFormat;
    /** @var string */
    protected $dateTimeFormat;

    /**
     * DtoManager constructor.
     * @param RequestManager $requestManager
     * @param DtoValidator $dtoValidator
     * @param DtoHelper $dtoHelper
     * @param FileLocator $fileLocator
     * @param string $configPath
     */
    public function __construct(
        RequestManager $requestManager,
        DtoValidator $dtoValidator,
        DtoHelper $dtoHelper,
        FileLocator $fileLocator,
        $dateFormat,
        $dateTimeFormat,
        $configPath
    ) {
        $this->requestManager = $requestManager;
        $this->dtoValidator = $dtoValidator;
        $this->dtoHelper = $dtoHelper;
        $this->fileLocator = $fileLocator;
        $this->dateFormat = $dateFormat;
        $this->dateTimeFormat = $dateTimeFormat;
        $this->configPath = $configPath;
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

        $dtoData = [];
        foreach ($dtoConfig[$dtoType]['fields'] as $field => $options) {
            $getter = isset($options['getter']) ? $options['getter'] : $this->dtoHelper->getFieldGetter($field);
            $value = call_user_func([$data, $getter]);
            $dtoData[$field] = $this->castValueType($options['type'], $value);
        }

        return new Dto($dtoData);
    }

    public function createDtoCollection(array $data, $dtoType)
    {

    }

    /**
     * @return array
     */
    protected function getDtoConfig()
    {
        // TODO: caching
        if ($this->dtoConfig === null) {
            $absolutePath = $this->fileLocator->locate($this->configPath);
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
                $value = $value->format($this->dateFormat);
                break;
            case DtoInterface::TYPE_DATE_TIME:
                if (!$value instanceof \DateTime) {
                    $value = new \DateTime($value);
                }
                $value = $value->format($this->dateTimeFormat);
                break;
            default:
                $value = null;

        }

        return $value;
    }
}
