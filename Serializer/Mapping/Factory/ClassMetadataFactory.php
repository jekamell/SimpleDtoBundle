<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Factory;

use Doctrine\Common\Cache\Cache;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Symfony\Component\Serializer\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassResolverTrait;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

/**
 * Class ClassMetadataFactory
 * This ClassMetadataFactory use decorated ClassMetadata
 */
class ClassMetadataFactory extends \Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory
{
    use ClassResolverTrait;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var array
     */
    private $loadedClasses;

    /**
     * @param LoaderInterface $loader
     * @param Cache|null      $cache
     */
    public function __construct(LoaderInterface $loader, Cache $cache = null)
    {
        $this->loader = $loader;
        $this->cache = $cache;

        if (null !== $cache) {
            @trigger_error(sprintf('Passing a Doctrine Cache instance as 2nd parameter of the "%s" constructor is deprecated since version 3.1. This parameter will be removed in Symfony 4.0. Use the "%s" class instead.', __CLASS__, CacheClassMetadataFactory::class), E_USER_DEPRECATED);
        }

        parent::__construct($loader, $cache);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value): ClassMetadataDecorator
    {
        $class = $this->getClass($value);

        if (isset($this->loadedClasses[$class])) {
            return $this->loadedClasses[$class];
        }

        if ($this->cache && ($this->loadedClasses[$class] = $this->cache->fetch($class))) {
            return $this->loadedClasses[$class];
        }

        $classMetadata = new ClassMetadataDecorator(new ClassMetadata($class));
        $this->loader->loadClassMetadata($classMetadata);

        $reflectionClass = $classMetadata->getReflectionClass();

        // Include metadata from the parent class
        if ($parent = $reflectionClass->getParentClass()) {
            $classMetadata->merge($this->getMetadataFor($parent->name));
        }

        // Include metadata from all implemented interfaces
        foreach ($reflectionClass->getInterfaces() as $interface) {
            $classMetadata->merge($this->getMetadataFor($interface->name));
        }

        if ($this->cache) {
            $this->cache->save($class, $classMetadata);
        }

        return $this->loadedClasses[$class] = $classMetadata;
    }
}
