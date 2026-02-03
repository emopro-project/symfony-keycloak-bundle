<?php

namespace KeycloakAuthBundle\Infrastructure\Keycloak;

use KeycloakAuthBundle\Domain\Port\HealthCheckInterface as PortHealthCheckInterface;
use KeycloakAuthBundle\Domain\Model\HealthStatus;
use KeycloakAuthBundle\Domain\Port\JwksProviderInterface;
use KeycloakAuthBundle\Infrastructure\Monitoring\PrometheusCounter;


class KeyCloackHealthChecker implements PortHealthCheckInterface
{


    public function __construct(
        private JwksProviderInterface $jwksProvider,
        private IssuerChecker $issuerChecker,
        private  PrometheusCounter $prometheus,
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

        $durationMs = (microtime(true) - $start) * 1000;
        $this->prometheus->health($ok);
        $this->prometheus->observeDurations($durationMs);

        return new HealthStatus(
            $ok,
            $details,
            [$durationMs]
        );
    }






    
}
