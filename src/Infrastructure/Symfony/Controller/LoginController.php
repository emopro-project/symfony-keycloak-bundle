<?php

namespace Vendor\SymfonyKeycloakBundle\Infrastructure\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route(
        path: '/login/check',
        name: 'keycloak_login_check_2',
        methods: ['GET']
    )]

    public function check(): Response
    {
        return new Response('Keycloak login check');
    }
}

