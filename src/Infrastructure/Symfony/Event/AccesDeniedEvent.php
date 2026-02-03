<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Event;


final class AccesDeniedEvent
{
    const EVENT_NAME = 'acces.denied.event';
    public function __construct(
        private string $ip,
        private ?string $userId,
        private ?string $reason
    ) {}

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

}
