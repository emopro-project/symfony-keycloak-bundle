<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak\Jwt;

use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\RSAKey;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface;
use KeycloakAuthBundle\Domain\Port\RoleMapperInterface;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


final class LcobucciJwtValidator implements TokenValidatorInterface
{
    public function __construct(
        private Configuration $jwtConfig,
        private JwksProviderInterface $jwksProvider,
        private RoleMapperInterface $roleMapper,
        private string $issuer,
    ) {}

    public function validate(string $token): AuthenticatedUser
    {
        try {
            $jwt = $this->jwtConfig->parser()->parse($token);
            $getKeys = $this->getKeyFor($jwt);
            $this->validateClaims($token);
            $this->jwtConfig->validator()->assert(
                $jwt,
                new IssuedBy($this->issuer),
                new ValidAt(SystemClock::fromUTC()),
                new SignedWith(
                    $this->jwtConfig->signer(),
                    $getKeys
                )
            );
        } catch (\Throwable $e) {
            throw new AuthenticationException('Invalid JWT', 0, $e);
        }
        /** @var \Lcobucci\JWT\Token\Plain $jwt */
        $claims   = $jwt->claims()->all(); // tableau associatif
        $roles    = $this->roleMapper->map($claims['realm_access']['roles'], $claims['realm_access']['roles']);
        $userName = $claims['preferred_username'] ??  $claims['username'] ?? "unknow";

        return new AuthenticatedUser(
            id: $jwt->claims()->get('sub'),
            username: $userName,
            roles: $roles,
            attributes: []
        );
    }


    public function formatToken(string $token): string
    {
        return trim(preg_replace('/^(?:\s+)?[B-b]earer\s/', '', $token));
    }


    public function getKeyFor(Token $token): InMemory
    {

        $kid = $token->headers()->get('kid');

        if (!$kid) {
            throw new \RuntimeException('JWT has no kid header');
        }

        $jwks = $this->jwksProvider->getJwks();

        foreach ($jwks->keys as $key) {
            if (($key->kid ?? null) === $kid) {
                return InMemory::plainText(
                    RSAKey::createFromJWK(new JWK((array) $key))->toPEM()
                );
            }
        }
        throw new \RuntimeException(sprintf('No JWKS key found for kid "%s"', $kid));
    }


    public function validateClaims(string $token): void
    {
        try {
            /** @var Plain $jwt */
            $jwt = $this->jwtConfig->parser()->parse($token);
        } catch (\Throwable $e) {
            throw new AuthenticationException('Malformed JWT', 0, $e);
        }

        $claims = $jwt->claims();

        if ($claims->get('typ') !== 'Bearer') {
            throw new AuthenticationException('Invalid token type');
        }

        // // azp = client_id
        // if ($claims->get('azp') !== $this->clientId) {
        //     throw new AuthenticationException('Invalid authorized party');
        // }

        if (!$claims->has('sub') || empty($claims->get('sub'))) {
            throw new AuthenticationException('Missing subject');
        }

        if ($claims->get('iss') !== $this->issuer) {
            throw new AuthenticationException('Invalid issuer');
        }
    }
}
