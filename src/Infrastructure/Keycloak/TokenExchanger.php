<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Keycloak;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vendor\SymfonyKeycloakBundle\Domain\Port\TokenExchangerInterface;

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



        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Unable to exchange authorization code');
        }
        return $response->toArray();
    }


    public function exchangePasswordForToken(string $username, string $password): array
    {

    try{

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
        }catch(Exception $e){
              throw new \RuntimeException($e->getMessage());
        }



        return $response->toArray();
    }
}
