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
        private string $clientId
    ) {}

    public function resolve(?string $username = null): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $path = $request->getPathInfo();
        if (!in_array($path, $this->allowedPaths, true)) {
            return null;
        }

        $ip   = $request->getClientIp() ?: 'unknown_ip';
        $userPart = $username ? sprintf('user:%s', $username) : 'anonymous';


       return sha1($ip . '|' . $this->clientId . '|' . $path . '|' . $userPart);
    }
}
