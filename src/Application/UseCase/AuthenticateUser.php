<?php

namespace Vendor\SymfonyKeycloakBundle\Application\UseCase;

use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;
use Vendor\SymfonyKeycloakBundle\Domain\Port\TokenValidatorInterface;

class AuthenticateUser
{

    public function __construct(private readonly TokenValidatorInterface $validator)
    {
        // Initialize any required dependencies here
    }

    public function execute(string $accessToken): AuthenticatedUser
    {
        // Logic to authenticate user with Keycloak using the access token
        return $this->validator->validate($accessToken);
    }
}
