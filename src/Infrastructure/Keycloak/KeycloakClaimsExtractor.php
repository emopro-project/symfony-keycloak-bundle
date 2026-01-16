<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\RoleMapperInterface;;

final class KeycloakClaimsExtractor
{
    public function __construct(
        private RoleMapperInterface $roleMapper
    ) {}

    public function extract(array $claims): AuthenticatedUser
    {
        return new AuthenticatedUser(
            id: $claims['sub'],
            username: $claims['preferred_username'] ??  $claims['username'] ?? "unknow",
            roles: $this->roleMapper->map($claims['realm_access']['roles'], []),
            attributes: []
        );
    }
}
