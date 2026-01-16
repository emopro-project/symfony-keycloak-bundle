<?php

namespace KeycloakAuthBundle\Tests\Domain\Port;

use PHPUnit\Framework\TestCase;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface as PortJwksProviderInterface;

class JwksProviderInterfaceTest extends TestCase
{

    public function testgetJwksMethodsExist()
    {

        $jwksProviderInterfaceMoock = $this->createMock(PortJwksProviderInterface::class);
        $jwksProviderInterfaceMoock->method('getJwks')->willReturn(["fake jsk keycloak"]);
        $jwks = $jwksProviderInterfaceMoock->getJwks();
        $this->assertSame(["fake jsk keycloak"], $jwks);
        $this->isString($jwks);
    }
}
