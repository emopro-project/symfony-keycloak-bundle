<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '%keycloak.metrics.path%', name: 'prometheus_auth_metric', methods: ['GET'])]
class MetricsController extends AbstractController
{

    public function __construct(
        private CollectorRegistry $registry
    ) {}

    public function __invoke(): Response
    {

        $renderer = new RenderTextFormat();
        $metrics  = $this->registry->getMetricFamilySamples();
        return new Response(
            $renderer->render($metrics),
            200,
            ['Content-Type' => RenderTextFormat::MIME_TYPE]
        );
    }
}
