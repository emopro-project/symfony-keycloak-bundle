<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class IssuerChecker
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $realm
    ) {}

    public function check(): void
    {
        $uri = rtrim($this->baseUrl, '/') . '/realms/' . $this->realm . '/.well-known/openid-configuration';

        try {
            $response = $this->httpClient->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('Issuer Not Reachable');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Issuer Not Reachable: ' . $e->getMessage());
        }

        $data = $response->toArray(false);
        if (($data['issuer'] ?? null) !== $this->baseUrl)
            throw new RuntimeException('Issuer mismatch');
    }
}
