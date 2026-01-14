<?php

namespace Vendor\SymfonyKeycloakBundle\Application\UseCase;

use Vendor\SymfonyKeycloakBundle\Domain\Port\ClientCredentialsTokenProviderInterface;

final class GetClientCredentialsToken
{
    public function __construct(
        private ClientCredentialsTokenProviderInterface $provider
    ) {}

    public function execute(): string
    {
        return $this->provider->getToken();
    }
}
