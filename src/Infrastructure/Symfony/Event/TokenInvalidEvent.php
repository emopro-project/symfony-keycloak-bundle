<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Event;


final class TokenInvalidEvent
{
    const EVENT_NAME = 'token.invalid.event';
    public function __construct(
        private string $ip,
        private ?string $reason
    ) {}

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

}