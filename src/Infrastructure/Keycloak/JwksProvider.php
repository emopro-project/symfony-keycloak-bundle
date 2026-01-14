<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Keycloak;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vendor\SymfonyKeycloakBundle\Domain\Port\JwksProviderInterface;

final class JwksProvider implements JwksProviderInterface
{

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private string $jwksUrl,
    ) {}


    public  function getJwks(): object
    {
        $response = $this->httpClient->request('GET', $this->jwksUrl);
        if (200 !== $response->getStatusCode()) {
            throw new AuthenticationException('Impossible de récupérer le JWKS');
        }

        return json_decode($response->getContent());
    }

    public function findKeyByKid(object $jwks, string $kid): ?object
    {
        foreach ($jwks->keys as $key) {
            if ($key->kid === $kid) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Convertit un certificat x5c en clé publique PEM.
     */
    public function certToPem(string $cert): string
    {
        $cert = chunk_split($cert, 64, "\n");
        $pem = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";
        return $pem;
    }


    
}
