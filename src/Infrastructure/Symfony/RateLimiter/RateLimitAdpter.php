<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter;

use KeycloakAuthBundle\Domain\Port\RateLimiterCheckerInterface;
use KeycloakAuthBundle\Domain\Port\RateLimitKeyResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class RateLimitAdpter implements RateLimiterCheckerInterface
{

    public function __construct(
        private readonly RateLimiterFactory $factory,
        private readonly RateLimitKeyResolverInterface $keyResolver
    ) {}


    public function check(?string $username): void
    {
        $key = $this->keyResolver->resolve($username);
        $limiter = $this->factory->create($key);
        $consumption = $limiter->consume();

        if (!$consumption->isAccepted()) {
            throw new CustomUserMessageAuthenticationException(
                'Too many attempts. Please try again later.'
            );
        }
    }
}
