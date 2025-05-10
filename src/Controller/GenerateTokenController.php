<?php

namespace App\Controller;

use App\Service\JwtGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateTokenController extends AbstractController
{
     #[Route('/generate/token/all', name: 'app_generate_token')]
    public function getMercureTokenPublisher(JwtGenerator $jwtGenerator): JsonResponse
    {
        $token = $jwtGenerator->generateToken([
            '*'
        ]);

        return new JsonResponse(['token' => $token], Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }

     #[Route('/generate/token/suscriber', name: 'app_generate_publisher_token')]
    public function getMercureTokenSuscriber(JwtGenerator $jwtGenerator): JsonResponse
    {
        $token = $jwtGenerator->generateSuscriberToken([
            'http://localhost:8081/conversations*'
        ]);

        return new JsonResponse(['token' => $token], Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
