<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\Models;

use Symfony\Component\Security\Core\User\UserInterface;
use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;

final class SymfonyUser implements UserInterface
{
    public function __construct(
        private AuthenticatedUser $user,
        private string $accesToken
    ) {}

    public function getUserIdentifier(): string
    {
        return $this->user->getId();
    }

    public function getRoles(): array
    {
        return $this->user->getRoles();
    }

    public function eraseCredentials(): void
    {
        // nothing to do
    }

    public function getDomainUser(): AuthenticatedUser
    {
        return $this->user;
    }

    public function getAccessToken(): string { return $this->accesToken; }
}
