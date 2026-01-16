<?php

namespace KeycloakAuthBundle\Domain\Port;

use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;

interface TokenValidatorInterface
{
    public function validate(string $token): AuthenticatedUser;
    public function formatToken(string $token): string;
}
