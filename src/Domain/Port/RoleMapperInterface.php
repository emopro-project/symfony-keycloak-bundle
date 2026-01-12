<?php

namespace Vendor\SymfonyKeycloakBundle\Domain\Port;

interface RoleMapperInterface
{
    public function map(array $keycloakRoles): array;
}
