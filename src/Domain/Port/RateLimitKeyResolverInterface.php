<?php
namespace KeycloakAuthBundle\Domain\Port;

interface RateLimitKeyResolverInterface
{
    public function resolve(?string $id = null);
}