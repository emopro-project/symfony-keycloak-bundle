<?php

namespace KeycloakAuthBundle\Tests\Domain\Port;

use PHPUnit\Framework\TestCase;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface as PortJwksProviderInterface;
use stdClass;

class JwksProviderInterfaceTest extends TestCase
{

    public function testgetJwksMethodsExist()
    {

        $jwksProviderInterfaceMoock = $this->createMock(PortJwksProviderInterface::class);
        $jwksProviderInterfaceMoock->method('getJwks')->willReturn( new stdClass());
        $this->assertIsObject($jwksProviderInterfaceMoock->getJwks());
    }
}
