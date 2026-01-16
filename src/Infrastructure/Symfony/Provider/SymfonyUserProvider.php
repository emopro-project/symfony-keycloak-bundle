<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\Provider;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use KeycloakAuthBundle\Infrastructure\Symfony\Models\SymfonyUser;

final class SymfonyUserProvider implements UserProviderInterface
{
    public function supportsClass(string $class): bool
    {
        return $class === SymfonyUser::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new UserNotFoundException(
            'SymfonyUser is loaded only via Keycloak authenticator'
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SymfonyUser) {
            throw new \InvalidArgumentException('Unsupported user type');
        }

        return $user; // ✅ stateless / session-based
    }
}
