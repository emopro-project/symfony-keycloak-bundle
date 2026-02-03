<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\RateLimiter;

use KeycloakAuthBundle\Domain\Port\RateLimiterCheckerInterface;
use KeycloakAuthBundle\Domain\Port\RateLimitKeyResolverInterface;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\RateLimitExceedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class RateLimitAdapter implements RateLimiterCheckerInterface
{
    const REASON = 'Too many attempts. Please try again later.';
    public function __construct(
        private readonly RateLimiterFactory $factory,
        private readonly RateLimitKeyResolverInterface $keyResolver,
        private readonly EventDispatcherInterface $eventDispatcher,
        private RequestStack $requestStack,
    ) {}


    public function check(?string $id): void
    {
        $key = $this->keyResolver->resolve($id);
        $limiter = $this->factory->create($key);
        $consumption = $limiter->consume();

        $request = $this->requestStack->getCurrentRequest();
        $ip   = $request->getClientIp() ?: 'unknown_ip';

        if (!$consumption->isAccepted()) {
            $this->eventDispatcher->dispatch(new RateLimitExceedEvent(
                $key,
                $id,
                $ip,
                self::REASON
            ), RateLimitExceedEvent::class);
            throw new CustomUserMessageAuthenticationException(
                self::REASON
            );
        }
    }
}
