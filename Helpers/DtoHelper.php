<?php

namespace Mell\Bundle\SimpleDtoBundle\Helpers;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class DtoHelper
{
    /** @var  FileLocator */
    protected $fileLocator;
    /** @var string */
    protected $configPath;
    /** @var array */
    protected $dtoConfig;

    /**
     * DtoHelper constructor.
     * @param FileLocator $fileLocator
     * @param string $configPath
     */
    public function __construct(FileLocator $fileLocator, $configPath)
    {
        $this->fileLocator = $fileLocator;
        $this->configPath = $configPath;
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldGetter($field)
    {
        return 'get' . ucfirst($field);
    }

    /**
     * @param $field
     * @return string
     */
    public function getFieldSetter($field)
    {
        return 'set' . ucfirst($field);
    }

    /**
     * @return array
     */
    public function getDtoConfig()
    {
        // TODO: caching
        if ($this->dtoConfig === null) {
            $absolutePath = $this->fileLocator->locate($this->configPath);
            $this->dtoConfig = Yaml::parse(file_get_contents($absolutePath));
        }

        return $this->dtoConfig;
    }
}
