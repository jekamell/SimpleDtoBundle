<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection;

use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Loader\XmlLoader;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Loader\YamlLoader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

/**
 * Class ChainLoaderPass
 */
class ChainSerializerLoaderPass implements CompilerPassInterface
{
    /** @var array */
    private $overriddenLoaderClasses = [XmlFileLoader::class, YamlFileLoader::class];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $chainLoader = $container->getDefinition('serializer.mapping.chain_loader');

        $simpleDtoLoaders = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirName = $bundle['path'];
            if (is_file($file = $dirName.'/Resources/config/serialization.yml')) {
                $definition = new Definition(YamlLoader::class, array($file));
                $definition->setPublic(false);

                $simpleDtoLoaders[] = $definition;
                $container->addResource(new FileResource($file));
            }
            if (is_dir($dir = $dirName.'/Resources/config/serialization')) {
                /** @var \SplFileInfo $file */
                foreach (Finder::create()->files()->in($dir)->name('*.yml') as $file) {
                    $definition = new Definition(YamlLoader::class, array($file->getPathname()));
                    $definition->setPublic(false);

                    $simpleDtoLoaders[] = $definition;
                }

                $container->addResource(new DirectoryResource($dir));
            }
            if (is_file($file = $dirName.'/Resources/config/serialization.xml')) {
                $simpleDtoLoaders[] = (new Definition(XmlLoader::class, array($file)))->setPublic(false);
                $container->addResource(new FileResource($file));
            }
            if (is_dir($dir = $dirName.'/Resources/config/serialization')) {
                /** @var \SplFileInfo $file */
                foreach (Finder::create()->files()->in($dir)->name('*.xml') as $file) {
                    $simpleDtoLoaders[] = (new Definition(XmlLoader::class, array($file->getPathname())))->setPublic(false);
                }

                $container->addResource(new DirectoryResource($dir));
            }

        }

        $loaders = array_filter(
            array_merge($simpleDtoLoaders, $chainLoader->getArgument(0)),
            function (Definition $loaderDefinition) {
                return !in_array($loaderDefinition->getClass(), $this->overriddenLoaderClasses);
            }
        );

        $chainLoader->replaceArgument(0, $loaders);
    }

    /**
     * @param string $class
     * @param string $configPath
     * @return Definition
     */
    protected function defineLoader(string $class, string $configPath): Definition
    {
        return (new Definition($class, $configPath))->setPublic(false);
    }
}
