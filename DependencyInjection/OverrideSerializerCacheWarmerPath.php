<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection;

use Mell\Bundle\SimpleDtoBundle\CacheWarmer\SerializerCacheWarmer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class OverrideSerializerCacheWarmerPath
 */
class OverrideSerializerCacheWarmerPath implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $serializerCacheWarmer =    $container->getDefinition('serializer.mapping.cache_warmer');
        $serializerCacheWarmer->setClass(SerializerCacheWarmer::class);
    }
}
