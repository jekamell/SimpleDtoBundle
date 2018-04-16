<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle;

use Mell\Bundle\SimpleDtoBundle\DependencyInjection\ChainSerializerLoaderPass;
use Mell\Bundle\SimpleDtoBundle\DependencyInjection\OverrideSerializerCacheWarmerPath;
use Mell\Bundle\SimpleDtoBundle\DependencyInjection\OverrideSerializerMetadataFactoryPath;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SimpleDtoBundle
 */
class SimpleDtoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ChainSerializerLoaderPass());
        $container->addCompilerPass(new OverrideSerializerMetadataFactoryPath());
        $container->addCompilerPass(new OverrideSerializerCacheWarmerPath());
    }
}
