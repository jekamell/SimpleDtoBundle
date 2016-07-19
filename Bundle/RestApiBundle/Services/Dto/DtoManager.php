<?php

namespace Mell\Bundle\RestApiBundle\Services\Dto;

use Mell\Bundle\RestApiBundle\Services\RequestManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class DtoManager
{
    /** @var RequestManager */
    protected $requestManager;
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
     * @param FileLocator $fileLocator
     * @param string $configPath
     */
    public function __construct(RequestManager $requestManager, FileLocator $fileLocator, $configPath)
    {
        $this->requestManager = $requestManager;
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
        if (!isset($dtoConfig[$dtoType])) {
            throw new \InvalidArgumentException(sprintf('Dto config not found: %s', $dtoType));
        }

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
}
