<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Listener;

use KeycloakAuthBundle\Infrastructure\Monitoring\PrometheusCounter as MonitoringPrometheusCounter;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\AccesDeniedEvent;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\LoginValidateEvent;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\RateLimitExceedEvent;
use KeycloakAuthBundle\Infrastructure\Symfony\Event\TokenValidEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KeycloakMetricsListener implements EventSubscriberInterface
{
    public function __construct(
        private MonitoringPrometheusCounter $prometheus,
        private LoggerInterface $logger
    ) {}


    public static function getSubscribedEvents(): array
    {
        return [
            RateLimitExceedEvent::class => "onRateLimitExceeded",
            TokenValidEvent::class => "onTokenValidated",
            AccesDeniedEvent::class => "onAccessDenied",
            LoginValidateEvent::class => "onLoginFailed",
            

        ];
    }

    public function onRateLimitExceeded(RateLimitExceedEvent $event): void
    {
        $this->prometheus->inc([
            'exceeded',
            $event->getUserId() ? 'user' : 'anonymous',
            $event->getIp(),
            $event->getReason(),
        ], MonitoringPrometheusCounter::TYPE_RATE_LIMIT);
        $this->logger->warning('onTokenValidated', [
            'ip' => $event->getIp(),
            'user' => $event->getUserId(),
            'reason' => $event->getReason(),
        ]);
    }

    public function onTokenValidated(TokenValidEvent $event): void
    {
        $this->prometheus->inc([
            'result',
            $event->getUserId() ?? 'anonymous',
        ], MonitoringPrometheusCounter::TYPE_LOGIN);

        $this->logger->warning('onTokenValidated', [
            'userId' => $event->getUserId(),
            'roles' => $event->getRoles(),
        ]);
    }

    public function onAccessDenied(AccesDeniedEvent $event): void
    {
        $this->prometheus->inc([
            'result' =>  'denied',
            'username' => $event->getUserId() ?? 'anonymous',
        ], MonitoringPrometheusCounter::TYPE_LOGIN);

        $this->logger->warning('onAccessDenied', [
            'userId' => $event->getUserId(),
            'reason' => $event->getReason(),
            'ip' => $event->getIp(),

        ]);
    }

    public function onLoginFailed(LoginValidateEvent $event): void
    {
        $this->prometheus->inc([
            'failed',
            $event->getUserId() ?? 'anonymous',
            $event->getReason() ?? 'unknown',
            $event->getIp(),
        ], MonitoringPrometheusCounter::TYPE_LOGIN);

        $this->logger->warning('onLoginFailed', [
            'userId' => $event->getUserId(),
            'reason' => $event->getReason(),
            'ip' => $event->getIp(),
        ]);
    }
}
