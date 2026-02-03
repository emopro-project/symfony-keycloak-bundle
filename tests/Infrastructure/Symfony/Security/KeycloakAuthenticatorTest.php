<?php

namespace KeycloakAuthBundle\Tests\Infrastructure\Symfony\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use KeycloakAuthBundle\Application\UseCase\AuthenticateUser;
use KeycloakAuthBundle\Application\UseCase\RateLimit;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser as User;
use KeycloakAuthBundle\Infrastructure\Keycloak\LoginUrlGenerator;
use KeycloakAuthBundle\Infrastructure\Symfony\Security\KeycloakAuthenticator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class KeycloakAuthenticatorTest extends TestCase
{

    public function  testAuthenticateThrowsExceptionIfNoToken(): void
    {

        $request = new HttpFoundationRequest();
        $loginUrlGenerator = $this->createMock(LoginUrlGenerator::class);
        $rateLimit = $this->createMock(RateLimit::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $authenticateUser = $this->createMock(AuthenticateUser::class);
        $keycloakAuthenticator = new KeycloakAuthenticator($authenticateUser, $loginUrlGenerator, $rateLimit, $eventDispatcher  );
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
