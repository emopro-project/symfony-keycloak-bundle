<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring;

use KeycloakAuthBundle\Domain\Model\HistogramInterface;
use KeycloakAuthBundle\Infrastructure\Monitoring\Metrics\MetricDefinitions;
use KeycloakAuthBundle\Infrastructure\Monitoring\Metrics\PrometheusMetricFactory;

final class PrometheusHistogram implements HistogramInterface
{
    const TYPE_HTTP_RESPONSE_TIME = 'http_response_time';

    public function __construct(
        private PrometheusMetricFactory $collector
    ) {}

    public function observe(array $name, float $value, array $labels = []): void
    {
            $this->collector->create([
                'type' => 'histogram',
                'namespace' => array_key_exists('namespace', $name) ? $name['namespace'] : 'keycloak',
                'name' => 'http_response_time_seconds',
                'help' => 'HTTP response time in seconds',
                'labels' => ['method', 'status', 'route'],
                'labelNames' => ['method', 'status', 'route'],
            ])->observe($value, $labels);
            return;
        
    }
}