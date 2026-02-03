<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring;

use KeycloakAuthBundle\Domain\Port\MetricsRegistryInterface;
use KeycloakAuthBundle\Infrastructure\Monitoring\Metrics\MetricDefinitions;
use KeycloakAuthBundle\Infrastructure\Monitoring\Metrics\PrometheusMetricFactory;

final class PrometheusCounter implements MetricsRegistryInterface
{
    const TYPE_RATE_LIMIT = "RateLimit";
    const TYPE_LOGIN = "Login";
    public const TYPE_HEALTH = 'health';
    public const TYPE_HTTP_REQUESTS = 'http_requests';
    public const TYPE_LOGIN_FAILED = 'http_login_failed';
    public const TOkEN_EXCHANGED = 'token_exchanged';

    public function __construct(
        private PrometheusMetricFactory $collector
    ) {}
    /**
     * @param array<string> $labels
     * @param string $type
     * @return void
     * 
     * */
    public function inc(array $labels = [], string $type = "RateLimit"): void
    {
        if ($type === self::TYPE_RATE_LIMIT) {
            $this->collector->create(MetricDefinitions::RATE_LIMIT)->inc($labels);
            return;
        }
        if ($type === self::TYPE_LOGIN) {
            $this->collector->create(MetricDefinitions::LOGIN)->inc($labels);
            return;
        }
        if ($type === self::TYPE_HTTP_REQUESTS) {
            $this->collector->create(MetricDefinitions::HTTP_REQUESTS)->inc($labels);
            return;
        }
        if ($type === self::TYPE_LOGIN_FAILED) {
            $this->collector->create(MetricDefinitions::LOGINFAILDED)->inc($labels);
            return;
        }
    }

    /**
     * @param bool $up
     * @return void
     * 
     * */
    public function health(bool $up): void
    {
        $this->collector->create(MetricDefinitions::HEALTH)->set($up ? 1 : 0, ['component' => 'keycloak']);
    }

    /**
     * @param float $durationMs
     * @return void
     * 
     * */
    public function observeDurations(float $durationMs): void
    {
        $this->observe(MetricDefinitions::HTTP_REQUESTS, $durationMs / 1000, ['component' => 'health_check']);
    }

    /**
     * @param array{name: string, help: string, labels: array<string>} $name
     * @param float $value
     * @param array<string, string> $labels
     * @return void
     * 
     * */
    public function observe(array $name, float $value, array $labels = []): void
    {
        $this->collector->create($name)->observe($value, $labels);
    }
}
