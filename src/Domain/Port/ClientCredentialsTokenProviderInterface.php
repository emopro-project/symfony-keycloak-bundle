<?php

namespace Vendor\SymfonyKeycloakBundle\Domain\Port;

interface ClientCredentialsTokenProviderInterface
{
    public function getToken(): string;
}
