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

            ->scalarNode("jwt_validator")
            ->defaultValue("firebase")
            ->end()

            ->arrayNode('health')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('enabled')
            ->defaultTrue()
            ->end()

            ->scalarNode('path')
            ->defaultValue('/keycloak/health')
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
