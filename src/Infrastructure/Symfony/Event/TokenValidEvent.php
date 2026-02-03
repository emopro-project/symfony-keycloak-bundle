<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Event;


final class TokenValidEvent
{
    const EVENT_NAME = 'token.valid.event';
    public function __construct(
        private ?string $userId,
        private ?array $roles
    ) {}

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

}