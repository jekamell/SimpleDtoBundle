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


        // Register serializer configuration from application bundles
        $simpleDtoLoaders = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirName = $bundle['path'];
            // Load serializer config from Resources/config/serialization.yml
            if (is_file($file = $dirName.'/Resources/config/serialization.yml')) {
                $this->registerLoaderFromFile($container, YamlLoader::class, $file, $simpleDtoLoaders);
            }
            // Load serializer config from Resources/config/serialization.yaml
            if (is_file($file = $dirName.'/Resources/config/serialization.yaml')) {
                $this->registerLoaderFromFile($container, YamlLoader::class, $file, $simpleDtoLoaders);
            }
            // Load serializer config from Resources/config/serialization.xml
            if (is_file($file = $dirName.'/Resources/config/serialization.xml')) {
                $this->registerLoaderFromFile($container, XmlLoader::class, $file, $simpleDtoLoaders);
            }
            // Load serializer config from Resources/config/ directory
            if (is_dir($dir = $dirName.'/Resources/config/serialization')) {
                $this->registerLoaderFromDir($container, $dir, $simpleDtoLoaders);
            }
        }

        // Register serializer configuration from application
        $projectDir = $container->getParameter('kernel.project_dir');

        if (is_file($file = $projectDir.'/src/Resources/config/serialization.yml')) {
            $this->registerLoaderFromFile($container, YamlLoader::class, $file, $simpleDtoLoaders);
        }
        if (is_file($file = $projectDir.'/src/Resources/config/serialization.yaml')) {
            $this->registerLoaderFromFile($container, YamlLoader::class, $file, $simpleDtoLoaders);
        }
        if (is_file($file = $projectDir.'/src/Resources/config/serialization.xml')) {
            $this->registerLoaderFromFile($container, XmlLoader::class, $file, $simpleDtoLoaders);
        }
        if (is_dir($dir = $projectDir.'/src/Resources/config/serialization')) {
            $this->registerLoaderFromDir($container, $dir, $simpleDtoLoaders);
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
     * Register configuration from given file
     * @param ContainerBuilder $container
     * @param string $loaderClass
     * @param string $file
     * @param array $loaders
     */
    protected function registerLoaderFromFile(ContainerBuilder $container, string $loaderClass, string $file, array &$loaders):void
    {
        $definition = new Definition($loaderClass, array($file));
        $definition->setPublic(false);

        $loaders[] = $definition;
        $container->addResource(new FileResource($file));
    }

    /**
     * Register configuration from given directory
     * @param ContainerBuilder $container
     * @param string $dir
     * @param array $loaders
     */
    protected function registerLoaderFromDir(ContainerBuilder $container, string $dir, array &$loaders): void
    {
        /** @var \SplFileInfo $file */
        foreach (Finder::create()->files()->in($dir)->name('/\.(xml|ya?ml)$/') as $file) {
            $this->registerLoaderFromFile(
                $container,
                $file->getExtension() === 'xml' ? XmlLoader::class : YamlLoader::class,
                $file->getRealPath(),
                $loaders
            );
        }

        $container->addResource(new DirectoryResource($dir));
    }
}
