<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use Exception;
use KeycloakAuthBundle\Domain\Port\MetricsRegistryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use KeycloakAuthBundle\Domain\Port\TokenExchangerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;


final class TokenExchanger implements TokenExchangerInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private KeycloakEndpoints $keycloakEndPoints,
        private readonly MetricsRegistryInterface $metricFactory,
        private LoggerInterface $logger,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri
    ) {}

    public function exchange(string $code): array
    {


        try {
            $start = microtime(true);
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
            $durationMs = (microtime(true) - $start) * 1000;
            $this->metricFactory->observe([
                'type' => 'histogram',
                'name' => 'keycloak_client_credentials_token_request_duration_ms',
                'help' => 'Duration of Keycloak client credentials token request in milliseconds',
                'namespace' => 'keycloak',
                'labels' => ['username'],
                'labelValues' => ['token_exchange'],
                'value' => $durationMs,
            ], $durationMs, ['token_exchange']);
            return $response->toArray() ;
        } catch (
            ClientExceptionInterface |
            ServerExceptionInterface |
            RedirectionExceptionInterface |
            TransportExceptionInterface $e
        ) {
            // Récupère le corps de la réponse si disponible
            $content = method_exists($e, 'getResponse') && $e->getResponse()
                ? $e->getResponse()->getContent(false)
                : $e->getMessage();
            // Symfony gère l'erreur via AuthenticationException
            $this->logger?->error('Keycloak token exchange failed', ['content' => $content]);
            $this->logger?->warning('Keycloak token exchange failed', ['content' => $content]);
            header("Location: /");
            die();
        } catch (Exception $e) {
            $this->logger?->error('Keycloak token exchange failed', ['content' => $e->getMessage()]);
            throw new \Symfony\Component\Security\Core\Exception\AuthenticationException(
                'Keycloak exchange error: ' .  $e->getMessage()
            );
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
        } catch (
            ClientExceptionInterface |
            ServerExceptionInterface |
            RedirectionExceptionInterface |
            TransportExceptionInterface $e
        ) {
            $content = method_exists($e, 'getResponse') && $e->getResponse()
                ? $e->getResponse()->getContent(false)
                : $e->getMessage();

            throw new \RuntimeException('Keycloak password grant error: ' . $content);
        } catch (Exception $e) {
            throw new \RuntimeException('Unexpected error: ' . $e->getMessage());
        }
    }
}
