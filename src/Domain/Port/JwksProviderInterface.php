<?php

namespace KeycloakAuthBundle\Domain\Port;

use Lcobucci\JWT\Signer\Key\InMemory;

interface JwksProviderInterface
{
    public function getJwks(): object;
    public function findKeyByKid(object $jwks, string $kid): ?object;
    public function certToPem(string $cert): string;
}
