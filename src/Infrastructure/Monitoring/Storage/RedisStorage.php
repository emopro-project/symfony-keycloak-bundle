<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring\Storage;

use Prometheus\Storage\Redis;
use Prometheus\Storage\Adapter;

final class RedisStorage implements PrometheusStorageInterface
{
    public function __construct(
        private string $host,
        private int $port,
        private string $prefix,
        private float $timeout,
        private float $readTimeout,
        private bool $persistentConnections
    ) {}

    public function getAdapter(): Adapter
    {
        return new Redis([
            'host' => $this->host,
            'port' => $this->port,
            'prefix' => $this->prefix,
            'timeout' => $this->timeout,
            'read_timeout' => $this->readTimeout,
            'persistent_connections' => $this->persistentConnections,
        ]);
    }
}
