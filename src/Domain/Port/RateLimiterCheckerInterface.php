<?php

namespace KeycloakAuthBundle\Domain\Port;

interface RateLimiterCheckerInterface
{
    /**
     * Vérifie si l’action est autorisée.
     *
     * @throws \App\Domain\Exception\RateLimitExceededException
     */
    public function check(?string $username): void;
}