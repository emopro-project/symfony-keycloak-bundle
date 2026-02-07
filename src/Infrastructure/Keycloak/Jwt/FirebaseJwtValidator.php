<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak\Jwt;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface;
use KeycloakAuthBundle\Domain\Port\RoleMapperInterface;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;

class FirebaseJwtValidator implements TokenValidatorInterface
{

    public function __construct(
        private JwksProviderInterface $jwksProvider,
        private RoleMapperInterface $roleMapper
    ) {}

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
        try {

            $id = $decoded->sub ?? '';
            $username = $decoded->preferred_username ?? '';
            $realmRessourcesAccess = $decoded->resource_access->account ?? [];
            $realmAccessRoles      = $decoded->realm_access->roles ?? [];
            $roles = $this->roleMapper->map($realmRessourcesAccess, $realmAccessRoles);
            return new AuthenticatedUser($id, $username, $roles );
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erreur lors du mapping du JWT en utilisateur: ' . $e->getMessage(), 0, $e);
        }
    }
}
