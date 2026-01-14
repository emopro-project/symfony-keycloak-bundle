<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Symfony\Models;

use Symfony\Component\Security\Core\User\UserInterface;
use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;

final class SymfonyUser implements UserInterface
{
    public function __construct(
        private AuthenticatedUser $user
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
}
