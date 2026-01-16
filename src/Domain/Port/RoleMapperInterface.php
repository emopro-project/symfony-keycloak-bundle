<?php

namespace KeycloakAuthBundle\Domain\Port;

interface RoleMapperInterface
{
   public function map(array|object $realmRessourcesAccess, array $realmAccessRoles): array;
}
