<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;


use KeycloakAuthBundle\Domain\Port\RoleMapperInterface;

final class RoleMapper implements RoleMapperInterface
{

    public function map(array|object $realmRessourcesAccess, array $realmAccessRoles): array
    {
        $roles = $realmAccessRoles ?? [];
        $realmRessourcesAccess = (array) $realmRessourcesAccess;

        foreach ($realmRessourcesAccess as $client) {
            $clientRoles = isset($client->roles) ? (array) $client->roles : [];
            $roles = array_merge($roles, $clientRoles);
        }

        $roles = array_unique(array_map(fn($r) => "ROLE_" . strtoupper($r), $roles));
        $roles[] = 'ROLE_USER';

        return $roles;
    }
}
