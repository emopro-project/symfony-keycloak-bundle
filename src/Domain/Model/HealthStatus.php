<?php

namespace KeycloakAuthBundle\Domain\Model;

class HealthStatus
{

    public function __construct(
        public string $ok,
        public array $details,
        public array $responseTimeMs
    ) {}
}
