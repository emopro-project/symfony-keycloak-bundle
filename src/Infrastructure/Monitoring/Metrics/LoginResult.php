<?php
namespace KeycloakAuthBundle\Infrastructure\Monitoring\Metrics;

enum LoginResult: string
{
    case VALIDATED = 'validated';
    case FAILED    = 'failed';
    case EXPIRED   = 'expired';
    case LOCKED    = 'locked';
}