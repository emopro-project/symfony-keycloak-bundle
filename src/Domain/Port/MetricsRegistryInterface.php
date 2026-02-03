<?php

namespace KeycloakAuthBundle\Domain\Port;

interface MetricsRegistryInterface
{
    public function  inc(array $labels, string $type): void;   
    public function observe(array $name, float $value, array $labels = []): void;

}
