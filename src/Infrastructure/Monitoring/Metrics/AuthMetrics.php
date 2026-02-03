<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring\Metrics;

use KeycloakAuthBundle\Domain\Port\MetricsRegistryInterface;
use KeycloakAuthBundle\Infrastructure\Monitoring\PrometheusCounter;

final class AuthMetrics
{
    public function __construct(private readonly MetricsRegistryInterface $metricFactory) {}

    public function incrementTokenExchanged(string $username, $type): void
    {
        $this->metricFactory->inc([
            $username
        ], PrometheusCounter::TOkEN_EXCHANGED);
    }

    public function incrementSuccessfulLogin(string $username): void
    {
        $this->metricFactory->inc([
            'successfull_login',
            $username
        ], PrometheusCounter::TYPE_LOGIN);
    }

    public function incrementFailedLogin(?string $username = null): void
    {
        $labels = [];
        $values = [];

        if ($username !== null) {
            $labels[] = 'username';
            $values[] = $username;
        }

        $this->metricFactory->inc([
            $username 
        ], PrometheusCounter::TYPE_LOGIN_FAILED);
    }


    public function observeAuthenticationDuration(float $durationSeconds, ?string $username = null): void
    {
        $labels = [];
        $values = [];

        if ($username !== null) {
            $labels[] = 'username';
            $values[] = $username;
        }

        $this->metricFactory->observe([
            'type' => 'histogram',
            'name' => 'authentication_duration_seconds',
            'help' => 'Duration of user authentication in seconds',
            'namespace' => 'keycloak',
            'labels' => $labels,
            'labelValues' => $values,
            'value' => $durationSeconds,
        ], $durationSeconds, $values);
    }
}
