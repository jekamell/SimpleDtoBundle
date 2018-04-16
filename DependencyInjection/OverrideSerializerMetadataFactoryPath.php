<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class OverrideSerializerMetadataFactoryPath
 */
class OverrideSerializerMetadataFactoryPath implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $metadataFactory = $container->getDefinition('serializer.mapping.class_metadata_factory');
        $metadataFactory->setClass(ClassMetadataFactory::class);
    }
}
