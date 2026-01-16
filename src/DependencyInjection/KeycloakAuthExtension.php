<?php

namespace KeycloakAuthBundle\DependencyInjection;

use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;
use KeycloakAuthBundle\Infrastructure\Keycloak\Jwt\FirebaseJwtValidator;
use KeycloakAuthBundle\Infrastructure\Keycloak\Jwt\LcobucciJwtValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class KeycloakAuthExtension extends Extension
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
        $health       = $config['health'];
        $issuer       = $config['issuer'];

        $container->setParameter('keycloak.realm', $realm);
        $container->setParameter('keycloak.client_id', $config['client_id']);
        $container->setParameter('keycloak.base_url', $baseUrl);
        $container->setParameter('keycloak.redirect_uri', $redirectUrl);
        $container->setParameter('keycloak.client_secret', $clientSecret);
        $container->setParameter('keycloak.health.enabled',  $health['enabled']);
        $container->setParameter('keycloak.health.path', $health['path']);
        $container->setParameter('keycloak.issuer', $config['issuer']);

        match ($config['jwt_validator']) {
            'firebase' =>  $container->setAlias(
                TokenValidatorInterface::class,
                FirebaseJwtValidator::class
            ),
            'lcobucci' => $container->setAlias(
                TokenValidatorInterface::class,
                LcobucciJwtValidator::class
            ),
            default =>  throw new \InvalidArgumentException(
                sprintf('unknow jwt_validator "%s"', $config['jwt_validator'])
            )
        };

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
