<?php

namespace KeycloakAuthBundle\Tests\Domain\Port;

use PHPUnit\Framework\TestCase;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;

class TokenValidatorInterfaceTest extends TestCase
{

    public function testMapMethodsExist()
    {

        $authenticatedUser = new AuthenticatedUser("120", "john", []);
        $tokenValidatorInterfaceMoock = $this->createMock(TokenValidatorInterface::class);
        $tokenValidatorInterfaceMoock->method('validate')->willReturn($authenticatedUser);
        $authUser = $tokenValidatorInterfaceMoock->validate("fakeJwtToken");
        $this->assertSame($authUser, $authenticatedUser);
        $this->assertNotNull($authUser);
    }

    public function testFormatTokenMethodsExist()
    {

        $authenticatedUser = new AuthenticatedUser("120", "john", []);
        $tokenValidatorInterfaceMoock = $this->createMock(TokenValidatorInterface::class);
        $tokenValidatorInterfaceMoock->method('formatToken')->willReturn("fake-token-format");
        $fakeTokenFormat = $tokenValidatorInterfaceMoock->formatToken("Bearer: token-pass");
        $this->assertSame("fake-token-format", $fakeTokenFormat);
        $this->assertNotNull($fakeTokenFormat);
        $this->assertNotEmpty($fakeTokenFormat);
    }
}
