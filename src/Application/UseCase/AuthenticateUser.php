<?php

namespace KeycloakAuthBundle\Application\UseCase;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;
use KeycloakAuthBundle\Domain\Port\TokenExchangerInterface;
use KeycloakAuthBundle\Domain\Port\TokenValidatorInterface;

class AuthenticateUser
{

    public function __construct(
        private readonly TokenValidatorInterface $validator,
        private readonly TokenExchangerInterface $tokenExchangerInterface,
        private readonly RateLimit $rateLimit
    ) {
        // Initialize any required dependencies here
    }

    public function execute(string $accessToken): AuthenticatedUser
    {
        // Logic to authenticate user with Keycloak using the access token
        return $this->validator->validate($accessToken);
    }



    public function exchangeCodeForToken(string $code): string
    {
        $this->rateLimit->execute();
        $data = $this->tokenExchangerInterface->exchange($code);

        if (!isset($data['access_token'])) {
            throw new AuthenticationException('Access token missing');
        }

        return $data['access_token'];
    }


    public function authenticateWithPassword(string $username, string $password): string
    {
        $this->rateLimit->execute($username);
        $tokenData = $this->tokenExchangerInterface->exchangePasswordForToken($username, $password);
        return $tokenData['access_token'];
    }
}
