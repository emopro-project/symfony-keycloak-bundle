<?php

namespace KeycloakAuthBundle\DependencyInjection;

use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;
use KeycloakAuthBundle\Infrastructure\Keycloak\Jwt\FirebaseJwtValidator;
use KeycloakAuthBundle\Infrastructure\Keycloak\Jwt\LcobucciJwtValidator;
use KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter\RateLimitAdpter;
use KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter\RateLimitHttpResolverAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

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
        $rateLimiter  = $config['rate_limiter'];
        # keycloak default configuration
        $container->setParameter('keycloak.realm', $realm);
        $container->setParameter('keycloak.client_id', $config['client_id']);
        $container->setParameter('keycloak.base_url', $baseUrl);
        $container->setParameter('keycloak.issuer', $config['issuer']);
        $container->setParameter('keycloak.redirect_uri', $redirectUrl);
        $container->setParameter('keycloak.client_secret', $clientSecret);
        # Heath Check
        $container->setParameter('keycloak.health.enabled',  $health['enabled']);
        $container->setParameter('keycloak.health.path', $health['path']);
        # Rate limit
        $container->setParameter('keycloak.rate_limiter.limit',  $rateLimiter['limit']);
        $container->setParameter('keycloak.rate_limiter.interval',  $rateLimiter['interval']);
        $container->setParameter('keycloak.rate_limiter.policy',  $rateLimiter['policy']);
        $container->setParameter('keycloak.rate_limiter.allowed_paths',  $rateLimiter['allowed_paths']);


        // stockage
        $container->register('keycloak.rate_limiter.storage', InMemoryStorage::class);
        $container->register('keycloak.rate_limiter.factory', RateLimiterFactory::class)
            ->setArguments([
                [
                    'id' => 'keycloak',
                    'policy' => $rateLimiter['policy'],
                    'limit' => $rateLimiter['limit'],
                    'interval' => $rateLimiter['interval'],
                ],
                new Reference('keycloak.rate_limiter.storage'), // 🔹 utiliser Reference ici
            ]);
        $container->register('rate_limite', RateLimitHttpResolverAdapter::class)
            ->setArguments([
                new Reference('request_stack'),
                '%keycloak.rate_limiter.allowed_paths%',
                '%keycloak.client_id%',
            ]);
        $container->register(RateLimitAdpter::class)
            ->setArguments([
                new Reference('keycloak.rate_limiter.factory'),
                new Reference('rate_limite')
            ]);



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
