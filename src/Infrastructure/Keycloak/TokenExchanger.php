<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use KeycloakAuthBundle\Domain\Port\TokenExchangerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

final class TokenExchanger implements TokenExchangerInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private KeycloakEndpoints $keycloakEndPoints,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {}

    public function exchange(string $code): array
    {
        try {
            $response = $this->client->request('POST', $this->keycloakEndPoints->token(), [
                'timeout' => 10,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            return $response->toArray();
        } catch (ClientExceptionInterface |
                 ServerExceptionInterface |
                 RedirectionExceptionInterface |
                 TransportExceptionInterface $e) {
            // Récupère le corps de la réponse si disponible
            $content = method_exists($e, 'getResponse') && $e->getResponse()
                ? $e->getResponse()->getContent(false)
                : $e->getMessage();

            throw new \RuntimeException('Keycloak exchange error: ' . $content);
        } catch (Exception $e) {
            throw new \RuntimeException('Unexpected error: ' . $e->getMessage());
        }
    }

    public function exchangePasswordForToken(string $username, string $password): array
    {
        try {
            $response = $this->client->request('POST', $this->keycloakEndPoints->token(), [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'password',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username' => $username,
                    'password' => $password,
                    'scope' => 'openid',
                ],
            ]);

            return $response->toArray();
        } catch (ClientExceptionInterface |
                 ServerExceptionInterface |
                 RedirectionExceptionInterface |
                 TransportExceptionInterface $e) {
            $content = method_exists($e, 'getResponse') && $e->getResponse()
                ? $e->getResponse()->getContent(false)
                : $e->getMessage();

            throw new \RuntimeException('Keycloak password grant error: ' . $content);
        } catch (Exception $e) {
            throw new \RuntimeException('Unexpected error: ' . $e->getMessage());
        }
    }
}
