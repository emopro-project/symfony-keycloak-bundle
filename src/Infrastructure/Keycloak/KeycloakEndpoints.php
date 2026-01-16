<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

final class KeycloakEndpoints
{
    public function __construct(
        private string $baseUrl,
        private string $realm
    ) {}

    public function token(): string
    {
        return sprintf(
            '%s/realms/%s/protocol/openid-connect/token',
            rtrim($this->baseUrl, '/'),
            $this->realm
        );
    }

    public function auth(): string
    {
        return sprintf(
            '%s/realms/%s/protocol/openid-connect/auth',
            rtrim($this->baseUrl, '/'),
            $this->realm
        );
    }
}
