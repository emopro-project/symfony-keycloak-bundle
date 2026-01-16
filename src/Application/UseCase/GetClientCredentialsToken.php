<?php

namespace KeycloakAuthBundle\Application\UseCase;

use KeycloakAuthBundle\Domain\Port\ClientCredentialsTokenProviderInterface;

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
