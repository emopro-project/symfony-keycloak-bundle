<?php

namespace KeycloakAuthBundle\Domain\Model;

class AuthenticatedUser
{

    public function __construct(
        private string $id,
        private string $username,
        private array $roles,
        private array $attributes = []
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function getRoles(): array
    {
        return $this->roles;
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'roles' => $this->roles,
        ];
    }
}
