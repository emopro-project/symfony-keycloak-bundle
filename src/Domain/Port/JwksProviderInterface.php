<?php

namespace Vendor\SymfonyKeycloakBundle\Domain\Port;

interface JwksProviderInterface
{
    public function getJwks(): array;
}
