<?php

namespace  KeycloakAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class TestController extends AbstractController
{

    #[Route('/api')]
    public function admin(): JsonResponse
    {

        return $this->json(["ok" => true]);
    }
}
