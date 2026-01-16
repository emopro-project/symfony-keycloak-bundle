<?php

namespace KeycloakAuthBundle\Tests\Domain\Port;

use PHPUnit\Framework\TestCase;
use KeycloakAuthBundle\Domain\Port\RoleMapperInterface;

class RoleMapperInterfaceTest extends TestCase
{

    public function testMapMethodsExist()
    {

        $roleMapperInterfaceMoock = $this->createMock(RoleMapperInterface::class);
        $roleMapperInterfaceMoock->method('map')->willReturn(["Role_admin", "Role_manager"]);
        $roles = $roleMapperInterfaceMoock->map(["admin", "manager"]);
        $this->assertSame($roles, ["Role_admin", "Role_manager"]);
        $this->assertIsArray($roles);
    }
}
