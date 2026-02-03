<?php

namespace KeycloakAuthBundle\Infrastructure\Symfony\Controller;

use KeycloakAuthBundle\Application\UseCase\AuthenticateUser;
use KeycloakAuthBundle\Application\UseCase\GetClientCredentialsToken as UseCaseGetClientCredentialsToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use KeycloakAuthBundle\Domain\Port\HealthCheckInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '%keycloak.health.path%', name: 'keycloak_health', methods: ['GET'])]
class KeycloakHealthController extends AbstractController
{

    public function __construct(
        private HealthCheckInterface $healthCheck,
        private UseCaseGetClientCredentialsToken $getClientCredential,
        private AuthenticateUser $authenticateUser
    ) {}

    public function __invoke(): JsonResponse
    {
    
        $status = ($this->healthCheck->check());
        $token = $this->getClientCredential->execute();
        if ($token) {
            $user = $this->authenticateUser->execute($token);
        }

        return new JsonResponse(
            [
                'status' => $status->ok ? "UP" : "DOWN",
                'details' => $status->details,
                'response_time_ms' => $status->responseTimeMs

            ]
        );
    }
}
