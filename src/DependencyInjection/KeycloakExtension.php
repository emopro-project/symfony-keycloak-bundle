<?php

namespace Vendor\SymfonyKeycloakBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class KeycloakExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $config = $this->processConfiguration(
            new Configuration(),
            $configs
        );

        $realm   = $config['realm'];
        $baseUrl = rtrim($config['base_url'], '/');
        $redirectUrl = rtrim($config['redirect_uri'], '/');
        $clientSecret = $config['client_secret'];

        $container->setParameter('keycloak.realm', $realm);
        $container->setParameter('keycloak.client_id', $config['client_id']);
        $container->setParameter('keycloak.base_url', $baseUrl);
        $container->setParameter('keycloak.redirect_uri', $redirectUrl);
        $container->setParameter('keycloak.client_secret', $clientSecret);

        $container->setParameter(
            'keycloak.jwks_url',
            sprintf(
                '%s/realms/%s/protocol/openid-connect/certs',
                $baseUrl,
                $realm
            )
        );
    }


    
}
