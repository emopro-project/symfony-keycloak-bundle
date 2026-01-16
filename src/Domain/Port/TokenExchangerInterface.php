<?php

namespace KeycloakAuthBundle\Domain\Port;

use KeycloakAuthBundle\Domain\Model\AuthenticatedUser;

interface TokenExchangerInterface
{
    public function  exchange(string $code): array;
    public function  exchangePasswordForToken(string $username, string $password): array;
}
