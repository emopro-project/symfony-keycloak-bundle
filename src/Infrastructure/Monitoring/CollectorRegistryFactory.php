<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring;

  
use KeycloakAuthBundle\Infrastructure\Monitoring\Storage\PrometheusStorageInterface;
use Prometheus\CollectorRegistry;

final class CollectorRegistryFactory
{
    public function __construct(private readonly PrometheusStorageInterface $prometheusStorageFactory) {}

    public function create(): CollectorRegistry
    {
        return new CollectorRegistry(
            $this->prometheusStorageFactory->getAdapter()
        );
    }
}
