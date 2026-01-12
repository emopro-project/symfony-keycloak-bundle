<?php

namespace Vendor\SymfonyKeycloakBundle\Domain\Port;

use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;

interface TokenValidatorInterface
{
    public function validate(string $token): AuthenticatedUser;
    public function formatToken(string $token): string;
}
