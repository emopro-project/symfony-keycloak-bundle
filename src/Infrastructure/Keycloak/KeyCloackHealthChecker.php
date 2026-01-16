<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use KeycloakAuthBundle\Domain\Port\HealthCheckInterface as PortHealthCheckInterface;
use KeycloakAuthBundle\Domain\Model\HealthStatus;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface;

class KeyCloackHealthChecker implements PortHealthCheckInterface
{


    public function __construct(
        private JwksProviderInterface $jwksProvider,
        private IssuerChecker $issuerChecker
    ) {}


    public function check(): HealthStatus
    {
        $start = microtime(true);
        $details = [];

        try {
            // check jwk
            $this->jwksProvider->getJwks();
            $details['jwks'] = 'ok';

            // check issuer
            $this->issuerChecker->check();
            $details['issuer'] = 'ok';
            $ok = true;
        } catch (\Throwable $e) {

            $details['error'] = $e->getMessage();
            $ok = false;
        }

        return new HealthStatus(
            $ok,
            $details,
            [(microtime(true) - $start) * 1000]
        );
    }
}
