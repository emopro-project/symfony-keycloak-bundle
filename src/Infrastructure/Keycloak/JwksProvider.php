<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface;

final class JwksProvider implements JwksProviderInterface
{

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private string $jwksUrl,
    ) {}


    public function getJwks(): object
    {
        try {
            $response = $this->httpClient->request('GET', $this->jwksUrl);

            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('Impossible de récupérer le JWKS, statut HTTP: ' . $response->getStatusCode());
            }

            $jwks = json_decode($response->getContent());

            if (null === $jwks) {
                throw new \RuntimeException('Impossible de décoder le JWKS JSON: ' . json_last_error_msg());
            }

            return $jwks;
        } catch (\Throwable $e) {
            // Ici tu peux logger l'erreur si nécessaire
            throw new \RuntimeException('Erreur lors de la récupération du JWKS: ' . $e->getMessage(), 0, $e);
        }
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
