<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Keycloak;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;
use Vendor\SymfonyKeycloakBundle\Domain\Port\JwksProviderInterface;
use Vendor\SymfonyKeycloakBundle\Domain\Port\TokenValidatorInterface;

class TokenValidator implements TokenValidatorInterface
{

    public function __construct(
        private JwksProviderInterface $jwksProvider
    ) {
        // Initialize any required dependencies here
    }

    public function validate(string $jwt): AuthenticatedUser
    {

        $header = $this->decodeHeader($jwt);
        $alg = $header->alg;

        

        $jwks = $this->jwksProvider->getJwks();

        $keyData = $this->jwksProvider->findKeyByKid($jwks, $header->kid);
        if (!$keyData) {
            throw new AuthenticationException('Clé JWT introuvable pour le kid donné.');
        }

        $publicKeyPem = $this->jwksProvider->certToPem($keyData->x5c[0]);

        try {
            $decoded = JWT::decode($jwt, new Key($publicKeyPem, $alg));
            $mapUser = $this->mapJwtToUser($decoded);
            
            return $mapUser;
        } catch (\Throwable $e) {
            throw new AuthenticationException('JWT invalide : ' . $e->getMessage());
        }
    }

    public function formatToken(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?[B-b]earer\s/', '', $token));
    }



    private function decodeHeader(string $jwt): object
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            throw new AuthenticationException('JWT malformé');
        }

        $decodedHeaderJson = base64_decode(strtr($parts[0], '-_', '+/'));
        $header = json_decode($decodedHeaderJson);

        if (!$header || !isset($header->alg)) {
            throw new AuthenticationException('Impossible de décoder l’en-tête JWT ou algorithme manquant');
        }

        return $header;
    }



    private function mapJwtToUser(\stdClass $decoded): AuthenticatedUser
    {
        $id = $decoded->sub ?? '';
        $username = $decoded->preferred_username ?? '';
        $roles = $decoded->realm_access->roles ?? [];
        // Fusionner les rôles des clients si nécessaire
        if (isset($decoded->resource_access)) {
            foreach ($decoded->resource_access as $client) {
                $roles = array_merge($roles, $client->roles ?? []);
            }
        }
        $roles = array_unique(array_map(fn($r) => "ROLE_" . strtoupper($r), $roles));
        $roles[] = 'ROLE_USER';

        return new AuthenticatedUser($id, $username, $roles);
    }
}
