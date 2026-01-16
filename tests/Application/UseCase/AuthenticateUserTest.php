<?php

namespace KeycloakAuthBundle\Tests\Application\UseCase;


use PHPUnit\Framework\TestCase;
use KeycloakAuthBundle\Application\UseCase\AuthenticateUser as AuthenticateUserUseCase;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;

class AuthenticateUserTest extends TestCase
{

    public function testExecute()
    {

        $authenticateUser = new AuthenticatedUser("user-id", "userName", []);
        $tokenValidatorInterface = $this->createMock(TokenValidatorInterface::class);
        $tokenValidatorInterface->method('validate')->willReturn($authenticateUser);
        $retrieveUser = (new AuthenticateUserUseCase($tokenValidatorInterface))->execute('fakeToken');
        $this->assertSame($retrieveUser,  $authenticateUser);
    }
}
