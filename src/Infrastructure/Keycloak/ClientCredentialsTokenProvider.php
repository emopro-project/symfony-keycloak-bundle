<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use KeycloakAuthBundle\Domain\Port\ClientCredentialsTokenProviderInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

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
        try {
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

            if (!isset($data['access_token'])) {
                throw new \RuntimeException('No access_token returned by Keycloak.');
            }

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
