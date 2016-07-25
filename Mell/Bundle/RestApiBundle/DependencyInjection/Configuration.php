<?php

namespace Mell\Bundle\RestApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mell_rest_api');

        $rootNode
            ->children()
                ->scalarNode('param_fields')
                    ->cannotBeEmpty()
                    ->defaultValue('_fields')
                    ->info('Get param for require response fields')
                    ->end()
                ->scalarNode('param_expands')
                    ->defaultvalue('_expands')
                    ->info('Get param for require embedded objects')
                    ->end()
                ->scalarNode('param_limit')
                    ->defaultValue('_limit')
                    ->info('Get param for define collection limit')
                    ->end()
                ->scalarNode('param_offset')
                    ->defaultValue('_offset')
                    ->info('Get param for define collection offset')
                    ->end()
                ->scalarNode('param_sort')
                    ->defaultValue('_sort')
                    ->info('Get param for define collection sorting')
                    ->end()
                ->scalarNode('dto_config_path')
                    ->isRequired()
                    ->info('Path to dto config. Alias can be used: @AppBundle/Resources/config/dto.yml')
                    ->end()
                ->scalarNode('jwt_public_path')
                    ->isRequired()
                    ->info('Path to jwt public key. Alias can be used: "%kernel.root_dir%/app/config/jwt_public.pem"')
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
