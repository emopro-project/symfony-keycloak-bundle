<?php
namespace Vendor\SymfonyKeycloakBundle\Tests\Application\UseCase;


use PHPUnit\Framework\TestCase;
use Vendor\SymfonyKeycloakBundle\Application\UseCase\AuthenticateUser as AuthenticateUserUseCase;
use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;
use Vendor\SymfonyKeycloakBundle\Domain\Port\TokenValidatorInterface;

class AuthenticateUserTest extends TestCase
{

    public function testExecute()
    {

        $authenticateUser = new AuthenticatedUser("user-id", "userName",[]);
        $tokenValidatorInterface = $this->createMock(TokenValidatorInterface::class);
        $tokenValidatorInterface->method('validate')->willReturn($authenticateUser);
        $retrieveUser = (new AuthenticateUserUseCase($tokenValidatorInterface))->execute('fakeToken');
        $this->assertSame($retrieveUser,  $authenticateUser );




    }
}
