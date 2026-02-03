<?php

namespace  KeycloakAuthBundle\Infrastructure\Symfony\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use KeycloakAuthBundle\Infrastructure\Monitoring\PrometheusCounter;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'kernel.response', method: 'onKernelResponse')]
final class HttpRequestCounterListener 
{
    public function __construct(private readonly PrometheusCounter $prometheusCounter) {}

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $method = $request->getMethod();
        $status = (string) $response->getStatusCode();
        $route = $request->attributes->get('_route', 'unknown');

        // Récupérer ou créer le compteur
        $this->prometheusCounter->inc([
            $method,
            $status,
            $route,
        ], PrometheusCounter::TYPE_HTTP_REQUESTS);
    }
}
