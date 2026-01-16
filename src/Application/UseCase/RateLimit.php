<?php

namespace KeycloakAuthBundle\Application\UseCase;

use KeycloakAuthBundle\Domain\Port\RateLimiterCheckerInterface;

final class RateLimit
{
    public function __construct(
        private readonly RateLimiterCheckerInterface $rateLimitChecker
    ) {}

    public function execute(?string $username = null): void
    {
        $this->rateLimitChecker->check($username);
    }
}
