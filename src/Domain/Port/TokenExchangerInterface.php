<?php

namespace Vendor\SymfonyKeycloakBundle\Domain\Port;

use Vendor\SymfonyKeycloakBundle\Domain\Model\AuthenticatedUser;

interface TokenExchangerInterface
{
    public function  exchange(string $code): array;
    public function  exchangePasswordForToken(string $username, string $password): array;
}
