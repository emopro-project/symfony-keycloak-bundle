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

    public function validate(string $token): AuthenticatedUser
    {
        // Implémentation de la validation du token Keycloak.
        try {
            // Décodez le jeton JWT
            $jwks = $this->jwksProvider->getJwks();
            $headers = new stdClass();
            $data = JWT::decode($token, new Key($jwks, 'HS256'), $headers);
        } catch (\Symfony\Component\Security\Core\Exception\AuthenticationException $e) {
            throw new AuthenticationException($e->getMessage());
        }

        $roles = isset($data['realm_access']['roles']) ? $data['realm_access']['roles'] : [];
        $username = $data['preferred_username'];
        $email = $data['email'];
        return new AuthenticatedUser("", $username, $roles);
    }

    public function formatToken(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?[B-b]earer\s/', '', $token));
    }
}
