<?php

namespace KeycloakAuthBundle\Domain\Port;

interface JwksProviderInterface
{
    public function getJwks(): object;
    public function findKeyByKid(object $jwks, string $kid): ?object;
    public function certToPem(string $cert): string;
}
