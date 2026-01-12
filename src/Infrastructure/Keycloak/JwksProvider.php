<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Keycloak;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vendor\SymfonyKeycloakBundle\Domain\Port\JwksProviderInterface;

final class JwksProvider implements JwksProviderInterface
{

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private string $jwksUrl,
    ) {}
    public function getJwks(): array
    {
        // Implementation to fetch JWKS from Keycloak server
        $response = $this->httpClient->request('GET', $this->jwksUrl);
        return $response->toArray();
    }
}
