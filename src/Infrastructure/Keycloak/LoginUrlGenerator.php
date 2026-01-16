<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use Symfony\Component\HttpFoundation\RequestStack;

final class LoginUrlGenerator
{
    public function __construct(
        private string $baseUrl,
        private string $realm,
        private string $clientId,
        private string $redirectUri
    ) {}

    public function generate(): string
    {
        $query = http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid profile email',
        ]);

        return sprintf(
            '%s/realms/%s/protocol/openid-connect/auth?%s',
            rtrim("http://localhost:8083", '/'),
            $this->realm,
            $query
        );
    }
}
