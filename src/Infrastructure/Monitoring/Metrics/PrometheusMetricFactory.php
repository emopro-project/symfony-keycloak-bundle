<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use RuntimeException;

final class PrometheusMetricFactory
{
    public function __construct(
        private CollectorRegistry $registry
    ) {}


    public function create(array $definition): Counter|Gauge|Histogram
    {
        return match ($definition['type']) {
            'counter' => $this->registry->getOrRegisterCounter(
                $definition['namespace'],
                $definition['name'],
                $definition['help'],
                $definition['labels']
            ),
            'gauge' => $this->registry->getOrRegisterGauge(
                $definition['namespace'],
                $definition['name'],
                $definition['help'],
                $definition['labels']
            ),
            'histogram' => $this->registry->getOrRegisterHistogram(
                $definition['namespace'],
                $definition['name'],
                $definition['help'],
                $definition['labels']
            ),
            default => throw new RuntimeException('Unknown metric type'),
        };
    }


    // public function loginCounter(): Counter
    // {
    //     $cfg = MetricDefinitions::LOGIN;

    //     return $this->registry->getOrRegisterCounter(
    //         $cfg['namespace'],
    //         $cfg['name'],
    //         $cfg['help'],
    //         $cfg['labels']
    //     );
    // }

    // public function rateLimitCounter(): Counter
    // {
    //     $cfg = MetricDefinitions::RATE_LIMIT;

    //     return $this->registry->getOrRegisterCounter(
    //         $cfg['namespace'],
    //         $cfg['name'],
    //         $cfg['help'],
    //         $cfg['labels']
    //     );
    // }


    // public function activeSessionsGauge(): Gauge
    // {
    //     $cfg = MetricDefinitions::ACTIVE_SESSIONS;

    //     return $this->registry->getOrRegisterGauge(
    //         $cfg['namespace'],
    //         $cfg['name'],
    //         $cfg['help'],
    //         $cfg['labels']
    //     );
    // }






}

