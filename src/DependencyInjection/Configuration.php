<?php

namespace KeycloakAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('keycloak_auth');
        $rootNode = $treeBuilder->getRootNode();

        // Racine
        $rootNode
            ->children()
                ->scalarNode('realm')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('client_id')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('base_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('issuer')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('client_secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('redirect_uri')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('jwt_validator')
                    ->defaultValue('firebase')
                ->end()
            ->end(); // fin des enfants racine

        // Health
        $rootNode
            ->children()
                ->arrayNode('health')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('path')->defaultValue('/keycloak/health')->end()
                    ->end()
                ->end()
            ->end();

        // Rate limiter
        $rootNode
            ->children()
                ->arrayNode('rate_limiter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('limit')->defaultValue(10)->end()
                        ->scalarNode('interval')->defaultValue('1 minute')->end()
                        ->scalarNode('policy')->defaultValue('sliding_window')->end()
                        ->arrayNode('allowed_paths')
                            ->scalarPrototype()->end()
                            ->defaultValue(['/login/check'])
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
