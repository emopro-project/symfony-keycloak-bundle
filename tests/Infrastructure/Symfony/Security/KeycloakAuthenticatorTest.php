<?php

namespace KeycloakAuthBundle\Tests\Infrastructure\Symfony\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use KeycloakAuthBundle\Application\UseCase\AuthenticateUser;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser as User;
use KeycloakAuthBundle\Infrastructure\Symfony\Security\KeycloakAuthenticator;

class KeycloakAuthenticatorTest extends TestCase
{

    public function  testAuthenticateThrowsExceptionIfNoToken(): void
    {

        $request = new HttpFoundationRequest();
        $authenticateUser = $this->createMock(AuthenticateUser::class);
        $keycloakAuthenticator = new KeycloakAuthenticator($authenticateUser);
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage("No Token provided");
        $keycloakAuthenticator->authenticate($request);
    }


    public function testAuthenticateValideToken(): void
    {

        $request = new HttpFoundationRequest();
        $request->headers->set('Authorization', 'Bearer faketoken');
        $user = new User("id", "username", ['ROLE_USER']);
        $authenticateUser = $this->createMock(AuthenticateUser::class);
        $authenticateUser->method("execute")->willReturn($user);
        $keycloakAuthenticator = new KeycloakAuthenticator($authenticateUser);
        $passport = $keycloakAuthenticator->authenticate($request);
        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame('id', $passport->getUser()->getUserIdentifier());
        $this->assertSame(['ROLE_USER'], $passport->getUser()->getRoles());
    }
}
