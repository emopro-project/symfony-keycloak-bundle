<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Event;


final class RateLimitExceedEvent
{
    const EVENT_NAME = 'ratelimite.exceed.event';
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
