<?php

namespace KeycloakAuthBundle\Domain\Port;

use KeycloakAuthBundle\Domain\Model\HealthStatus;

interface HealthCheckInterface
{
    public function check(): HealthStatus;
}
