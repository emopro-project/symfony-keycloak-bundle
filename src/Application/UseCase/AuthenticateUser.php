<?php

namespace KeycloakAuthBundle\Application\UseCase;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\TokenExchangerInterface;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\TokenValidEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AuthenticateUser
{

    public function __construct(
        private readonly TokenValidatorInterface $validator,
        private readonly TokenExchangerInterface $tokenExchangerInterface,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        // Initialize any required dependencies here
    }

    public function execute(string $accessToken): AuthenticatedUser
    {
        // Logic to authenticate user with Keycloak using the access token
        $user = $this->validator->validate($accessToken);

        $this->eventDispatcher->dispatch(
            new TokenValidEvent(
                $user->getUsername(),
                $user->getRoles()
            ),
            TokenValidEvent::class
        );

        return $user;
    }


    public function exchangeCodeForToken(string $code): string
    {
        
        $data = $this->tokenExchangerInterface->exchange($code);

       

        if (!isset($data['access_token'])) {
            throw new AuthenticationException('Access token missing');
        }

        return $data['access_token'];
    }


    public function authenticateWithPassword(string $username, string $password): string
    {
        $tokenData = $this->tokenExchangerInterface->exchangePasswordForToken($username, $password);
        return $tokenData['access_token'];
    }
}
