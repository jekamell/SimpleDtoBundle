<?php

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DtoBuilderCompiler implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('simple_dto.dto_manager')) {
            return;
        }

        $definition = $container->findDefinition('simple_dto.dto_manager');
        $builders = $container->findTaggedServiceIds('simple_dto.dto_builder');

        foreach ($builders as $id => $builder) {
            $definition->addMethodCall('addBuilder', [new Reference($id)]);
        }
    }
}
