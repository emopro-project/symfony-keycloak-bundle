<?php

namespace KeycloakAuthBundle\Domain\Port;

interface ClientCredentialsTokenProviderInterface
{
    public function getToken(): string;
}
