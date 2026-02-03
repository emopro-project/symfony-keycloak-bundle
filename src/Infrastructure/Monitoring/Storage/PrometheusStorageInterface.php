<?php

namespace KeycloakAuthBundle\Infrastructure\Monitoring\Storage;

use Prometheus\Storage\Adapter;

interface PrometheusStorageInterface
{
    public function getAdapter():Adapter;
}
