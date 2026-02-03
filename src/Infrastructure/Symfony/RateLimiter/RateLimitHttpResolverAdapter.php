<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter;

use KeycloakAuthBundle\Domain\Port\RateLimitKeyResolverInterface as PortRateLimitKeyResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class  RateLimitHttpResolverAdapter implements PortRateLimitKeyResolverInterface
{
    public function __construct(
        private RequestStack $requestStack,
        #[Autowire('%keycloak.rate_limiter.allowed_paths%')]
        private array $allowedPaths,
        #[Autowire('%keycloak.client_id%')]
        private string $clientId,
        #[Autowire('%keycloak.rate_limiter.strategies%')]
        private array $strategies,

    ) {}

    public function resolve(?string $id = null): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $path = $request->getPathInfo();
        if (!in_array($path, $this->allowedPaths, true)) {
            return null;
        }

        $parts = [];
        foreach ($this->strategies as $strategy) {
            match ($strategy) {
                'ip' => $this->addIp($parts),
                'client' => $this->addClient($parts),
                'user'   => $this->addUser($parts, $id),
                'realm'  => $this->addRealm($parts, $this->clientId),
                default => null
            };
        }
        return sha1(implode("|", $parts));
    }

    private function addIp(array &$parts): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $ip   = $request->getClientIp() ?: 'unknown_ip';
        $parts[] = 'ip:' . $ip;
    }

    private function addClient(array &$parts): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($client = $request->headers->get('X-Client-Id')) {
            $parts[] = 'client:' . $client;
        }
    }

    private function addUser(array &$parts,  string $id)
    {
        if ($id) {
            $parts[] = 'user:' . $id;
        }
    }

    private function addRealm(array &$parts,  string $realm)
    {
        if ($realm) {
            $parts[] = 'realm:' . strtolower($realm);
        }
    }

    
}
