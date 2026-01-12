<?php

namespace Vendor\SymfonyKeycloakBundle\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;

class AuthenticatedUserTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $user = new AuthenticatedUser(
            id: '123',
            username: 'jdoe',
            roles: ['ROLE_USER', 'ROLE_ADMIN'],
            attributes: ['email' => 'jdoe@example.com']
        );

        $this->assertSame('123', $user->getId());
        $this->assertSame('jdoe', $user->getUsername());
        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
        $this->assertSame(['email' => 'jdoe@example.com'], $user->getAttributes());
    }

    public function testHasRoleReturnsTrueWhenRoleExists(): void
    {
        $user = new AuthenticatedUser(
            '123',
            'jdoe',
            ['ROLE_USER', 'ROLE_ADMIN']
        );

        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
    }

    public function testHasRoleReturnsFalseWhenRoleDoesNotExist(): void
    {
        $user = new AuthenticatedUser(
            '123',
            'jdoe',
            ['ROLE_USER']
        );

        $this->assertFalse($user->hasRole('ROLE_SUPER_ADMIN'));
    }
}
