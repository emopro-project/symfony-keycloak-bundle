<?php

namespace KeycloakAuthBundle\DependencyInjection;

use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;
use KeycloakAuthBundle\Infrastructure\Keycloak\Jwt\FirebaseJwtValidator;
use KeycloakAuthBundle\Infrastructure\Keycloak\Jwt\LcobucciJwtValidator;
use KeycloakAuthBundle\Infrastructure\Monitoring\Storage\APCStorage;
use KeycloakAuthBundle\Infrastructure\Monitoring\Storage\PrometheusStorageInterface;
use KeycloakAuthBundle\Infrastructure\Monitoring\Storage\RedisStorage;
use KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter\RateLimitAdapter;
use KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter\RateLimitHttpResolverAdapter;
use Prometheus\Storage\Redis;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
        $strategies   = $config['rate_limiter']['strategies'];
        $metrics       = $config['metrics'];
        $redis = $config['prometheus']['storage']['redis'];
        $storage = $config['prometheus']['storage']['type'];
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
        # metrics metrics
        $container->setParameter('keycloak.metrics.path', $metrics['path']);
        $container->setParameter('keycloak.metrics.enabled', $metrics['enabled']);
        # Rate limit
        $container->setParameter('keycloak.rate_limiter.limit',  $rateLimiter['limit']);
        $container->setParameter('keycloak.rate_limiter.interval',  $rateLimiter['interval']);
        $container->setParameter('keycloak.rate_limiter.policy',  $rateLimiter['policy']);
        $container->setParameter('keycloak.rate_limiter.allowed_paths',  $rateLimiter['allowed_paths']);
        $container->setParameter('keycloak.rate_limiter.strategies', $strategies);

        #Prometheus Redis config

        $container->setParameter('keycloak.prometheus.storage.type', $storage);
        $container->setParameter('keycloak.prometheus.redis.host', $redis['host']);
        $container->setParameter('keycloak.prometheus.redis.port', $redis['port']);
        $container->setParameter('keycloak.prometheus.redis.prefix', $redis['prefix']);
        $container->setParameter('keycloak.prometheus.redis.timeout', $redis['timeout']);
        $container->setParameter('keycloak.prometheus.redis.read_timeout', $redis['read_timeout']);
        $container->setParameter(
            'keycloak.prometheus.redis.persistent_connections',
            $redis['persistent_connections']
        );

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
                '%keycloak.rate_limiter.strategies%',
            ]);
        $container->register(RateLimitAdapter::class)
            ->setArguments([
                new Reference('keycloak.rate_limiter.factory'),
                new Reference('rate_limite'),
                new Reference(EventDispatcherInterface::class),
                new Reference(RequestStack::class),
            ]);


        # JWT Validator
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

        # storage Loader for prometheus
        match ($storage) {
            'redis' => $container->setAlias(
                PrometheusStorageInterface::class,
                RedisStorage::class
            ),
            'apcu' => $container->setAlias(
                PrometheusStorageInterface::class,
                extension_loaded('apcu') ? APCStorage::class : RedisStorage::class
            ),
            default => throw new \InvalidArgumentException(
                sprintf('unknow prometheus storage type "%s"', $storage)
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
