<?php

namespace Vendor\SymfonyKeycloakBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    // Configuration class implementation

    public function getConfigTreeBuilder(): TreeBuilder
    {
        // Build and return the configuration tree
        $treeBuilder = new TreeBuilder('keycloak');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('realm')->isRequired()->end()
                ->scalarNode('client_id')->isRequired()->end()
                ->scalarNode('base_url')->isRequired()->end()
            ->end();

        return $treeBuilder;
    }


}   