<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Loader\YamlLoader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

/**
 * Class ChainLoaderPass
 */
class ChainLoaderPass implements CompilerPassInterface
{
    /**
     * TODO: add loaders for xml
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $chainLoader = $container->getDefinition('serializer.mapping.chain_loader');

        $loaders = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirName = $bundle['path'];
            if (is_file($file = $dirName.'/Resources/config/serialization.yml')) {
                $definition = new Definition(YamlLoader::class, array($file));
                $definition->setPublic(false);

                $loaders[] = $definition;
                $container->addResource(new FileResource($file));
            }
            if (is_dir($dir = $dirName.'/Resources/config/serialization')) {
                /** @var \SplFileInfo $file */
                foreach (Finder::create()->files()->in($dir)->name('*.yml') as $file) {
                    $definition = new Definition(YamlLoader::class, array($file->getPathname()));
                    $definition->setPublic(false);

                    $loaders[] = $definition;
                }

                $container->addResource(new DirectoryResource($dir));
            }
        }

        $chainLoader->replaceArgument(0, array_merge($loaders, $chainLoader->getArgument(0))); // order is important
    }
}
