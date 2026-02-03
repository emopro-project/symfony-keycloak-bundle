<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use KeycloakAuthBundle\Domain\Port\ClientCredentialsTokenProviderInterface;
use KeycloakAuthBundle\Domain\Port\MetricsRegistryInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

final class ClientCredentialsTokenProvider implements ClientCredentialsTokenProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly KeycloakEndpoints $endpoints,
        private readonly MetricsRegistryInterface $metricFactory,
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {}

    public function getToken(): string
    {
        try {
            $start = microtime(true);
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
            $durationMs = (microtime(true) - $start) * 1000;
            $data = $response->toArray();

            if (!isset($data['access_token'])) {
                throw new \RuntimeException('No access_token returned by Keycloak.');
            }
            $this->metricFactory->observe([
                'type' => 'histogram',
                'name' => 'keycloak_client_credentials_token_request_duration_ms',
                'help' => 'Duration of Keycloak client credentials token request in milliseconds',
                'labelNames' => [],
                'namespace' => 'keycloak',
                'labels' => [],
                'value' => $durationMs,
            ], $durationMs, []);
            return $data['access_token'];
        } catch (ClientExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | TransportExceptionInterface $e) {
            // Erreurs liées à la requête HTTP ou réponse invalide
            throw new \RuntimeException('Keycloak client credentials token request failed: ' . $e->getMessage());
        } catch (\Throwable $e) {
            // Toute autre erreur
            throw new \RuntimeException('Unexpected error while getting Keycloak token: ' . $e->getMessage());
        }
    }
}
