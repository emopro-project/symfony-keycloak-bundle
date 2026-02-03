<?php

namespace KeycloakAuthBundle\Tests\Application\UseCase;


use PHPUnit\Framework\TestCase;
use KeycloakAuthBundle\Application\UseCase\AuthenticateUser as AuthenticateUserUseCase;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use KeycloakAuthBundle\Domain\Port\TokenExchangerInterface;
class AuthenticateUserTest extends TestCase
{

    public function testExecute()
    {

        $authenticateUser = new AuthenticatedUser("user-id", "userName", []);
        $tokenValidatorInterface = $this->createMock(TokenValidatorInterface::class);
        $eventDispatcherInterface = $this->createMock(EventDispatcherInterface::class);
        $tokenExchangerInterface = $this->createMock(TokenExchangerInterface::class);


        $tokenValidatorInterface->method('validate')->willReturn($authenticateUser);
        $retrieveUser = (new AuthenticateUserUseCase($tokenValidatorInterface,  $tokenExchangerInterface, $eventDispatcherInterface ))->execute('fakeToken');
        $this->assertSame($retrieveUser,  $authenticateUser);
    }
}
