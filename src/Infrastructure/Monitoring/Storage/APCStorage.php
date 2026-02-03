<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring\Storage;

use Prometheus\Storage\Adapter;

final class APCStorage implements PrometheusStorageInterface
{
    public function getAdapter(): Adapter
    {
        return new \Prometheus\Storage\APC();
    }
}
