<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Services\RequestManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class DtoManager
{
    /** @var RequestManager */
    protected $requestManager;
    /** @var DtoValidator */
    protected $dtoValidator;
    /** @var Yaml */
    protected $yaml;
    /** @var  FileLocator */
    protected $fileLocator;
    /** @var string */
    protected $configPath;
    /** @var array */
    protected $dtoConfig;

    /**
     * DtoManager constructor.
     * @param RequestManager $requestManager
     * @param DtoValidator $dtoValidator
     * @param FileLocator $fileLocator
     * @param string $configPath
     */
    public function __construct(
        RequestManager $requestManager,
        DtoValidator $dtoValidator,
        FileLocator $fileLocator,
        $configPath
    ) {
        $this->requestManager = $requestManager;
        $this->dtoValidator = $dtoValidator;
        $this->fileLocator = $fileLocator;
        $this->configPath = $configPath;
    }

    /**
     * @param $data
     * @param $dtoType
     * @return array
     */
    public function createDto($data, $dtoType)
    {
        $dtoConfig = $this->getDtoConfig();
        $this->validateDto($dtoConfig, $dtoType, $data);


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
     * @param \stdClass $object
     */
    protected function validateDto($dtoConfig, $dtoType, $object)
    {
        $this->dtoValidator->validateDto($dtoConfig, $object, $dtoType);
    }
}
