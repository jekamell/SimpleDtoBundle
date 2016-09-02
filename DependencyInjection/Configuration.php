<?php

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Mell\Bundle\SimpleDtoBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('simple_dto');

        $rootNode
            ->children()
                ->scalarNode('param_fields')
                    ->cannotBeEmpty()
                    ->defaultValue('_fields')
                    ->info('Get param for require response fields')
                    ->end()
                ->scalarNode('param_expands')
                    ->defaultvalue('_expands')
                    ->info('_expands')
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
                ->scalarNode('hateoas_enabled')
                    ->defaultValue(false)
                    ->info('Whether the HATEOAS option is enabled')
                    ->end()
                ->scalarNode('auth_entity_alias')
                    ->info('Entity alias which used for authentication')
                    ->end()
                ->scalarNode('auth_username_fields')
                    ->info('Unique entity field for authenticate user')
                    ->end()
                ->variableNode('trusted_clients')
                    ->info('List of trusted clients in { id: id, name: name } format')
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
