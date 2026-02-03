<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring\Metrics;

final class MetricDefinitions
{
    public const LOGIN = [
        'type' => 'counter',
        'namespace' => 'keycloak',
        'name' => 'auth_successful_logins',
        'help' => 'Number of successful user logins',
        'labels' => ['result', 'username'],
    ];

    public const LOGINFAILDED = [
        'type' => 'counter',
        'namespace' => 'keycloak',
        'name' => 'auth_failed_logins',
        'help' => 'Number of failed user login attempts',
        'labels' => ['result', 'username', 'reason', 'ip'],
    ];

    public const RATE_LIMIT = [
        'type' => 'counter',
        'namespace' => 'security',
        'name' => 'rate_limit_exceeded_total',
        'help' => 'Number of rate limit exceeded',
        'labels' => ['result', 'user_id', 'ip', 'reason'],
    ];

    public const ACTIVE_SESSIONS = [
        'type' => 'gauge',
        'namespace' => 'keycloak',
        'name' => 'active_sessions',
        'help' => 'Number of active sessions',
        'labels' => ['realm', 'client'],
    ];


    public const HEALTH = [
        'type' => 'gauge',
        'namespace' => 'keycloak',
        'name' => 'service_up',
        'help' => 'Keycloak service health',
        'labels' => ['component'],
    ];


    public const HEALTH_RESPONSE_TIME = [
        'type' => 'histogram',
        'namespace' => 'keycloak',
        'name' => 'health_response_time_seconds',
        'help' => 'Keycloak health check response time',
        'labels' => ['component'],
    ];


    public const HTTP_REQUESTS = [
        'type' => 'counter',
        'namespace' => 'app',
        'name' => 'http_requests_total',
        'help' => 'Total number of HTTP requests',
        'labels' => ['method', 'status', 'route'],
    ];


    public const TOKEN_EXCHANGED = [
        'type' => 'counter',
        'namespace' => 'keycloak',
        'name' => 'tokens_exchanged_total',
        'help' => 'Number of tokens exchanged successfully',
        'labels' => ['username'],
    ];
}
