<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Keycloak;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vendor\SymfonyKeycloakBundle\Domain\Port\ClientCredentialsTokenProviderInterface;

final class ClientCredentialsTokenProvider implements ClientCredentialsTokenProviderInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private KeycloakEndpoints $endpoints,
        private string $clientId,
        private string $clientSecret
    ) {}

    public function getToken(): string
    {
        $response = $this->client->request('POST', $this->endpoints->token(), [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $data = $response->toArray();

        return $data['access_token'];
    }
}
