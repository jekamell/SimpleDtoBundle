<?php

namespace Mell\Bundle\SimpleDtoBundle\Helpers;

use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Symfony\Component\Config\FileLocator;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DtoHelper
 * @package Mell\Bundle\SimpleDtoBundle\Helpers
 */
class DtoHelper
{
    /** @var  FileLocator */
    protected $fileLocator;
    /** @var CacheItemPoolInterface */
    protected $cache;
    /** @var string */
    protected $configPath;
    /** @var array */
    protected $dtoConfig;
    /** @var string */
    protected $formatDate;
    /** @var string */
    protected $formatDateTime;

    /**
     * DtoHelper constructor.
     * @param FileLocator $fileLocator
     * @param CacheItemPoolInterface $cache
     * @param string $configPath
     * @param $formatDate
     * @param $formatDateTime
     */
    public function __construct(
        FileLocator $fileLocator,
        CacheItemPoolInterface $cache,
        $configPath,
        $formatDate,
        $formatDateTime
    ) {
        $this->fileLocator = $fileLocator;
        $this->cache = $cache;
        $this->configPath = $configPath;
        $this->formatDate = $formatDate;
        $this->formatDateTime = $formatDateTime;
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
        $config = $this->cache->getItem('simple_dto.config');
        if (!$config->isHit()) {
            $absolutePath = $this->fileLocator->locate($this->configPath);
            $config->set(Yaml::parse(file_get_contents($absolutePath)));
            $this->cache->save($config);
        }

        return $config->get();
    }

    /**
     * @param string $type
     * @param mixed $value
     * @param bool $raw
     * @return mixed
     */
    public function castValueType($type, $value, $raw = true)
    {
        if ($value === null) {
            return $value;
        }
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
                    $value = $value->format($this->formatDate);
                }
                break;
            case DtoInterface::TYPE_DATE_TIME:
                if (!$value instanceof \DateTime) {
                    $value = new \DateTime($value);
                }
                if ($raw) {
                    $value = $value->format($this->formatDateTime);
                }
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
