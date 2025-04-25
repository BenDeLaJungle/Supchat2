<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MercureController extends AbstractController
{
    private string $authSecret;
    private string $mercureSecret;

    public function __construct()
    {
        $this->authSecret = $_ENV['JWT_SECRET'] ?? 'default_auth_secret';
        $this->mercureSecret = $_ENV['MERCURE_JWT_SECRET'] ?? 'default_mercure_secret';
    }

    #[Route('/api/mercure-token', name: 'get_mercure_token', methods: ['GET'])]
    public function getMercureToken(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Token manquant'], 401);
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->authSecret, 'HS256'));

           
            $mercurePayload = [
                'mercure' => [
                    'subscribe' => ['channel/*'],
                ],
                'exp' => time() + 3600,
            ];

            $mercureToken = JWT::encode($mercurePayload, $this->mercureSecret, 'HS256');

            return new JsonResponse(['token' => $mercureToken]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token invalide'], 401);
        }
    }
}

