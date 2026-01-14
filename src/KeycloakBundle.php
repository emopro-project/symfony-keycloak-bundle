<?php

namespace Vendor\SymfonyKeycloakBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class KeycloakBundle extends Bundle
{


    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(
            __DIR__ . '/Resources/config/routes.yaml'
        );
    }
}
