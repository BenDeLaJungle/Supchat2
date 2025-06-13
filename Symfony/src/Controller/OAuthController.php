<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class OAuthController extends AbstractController
{
    private string $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'default_secret';
    }

    /**
     * Redirection vers Google pour l'authentification
     */
    #[Route('/api/auth/google', name: 'api_auth_google', methods: ['GET'])]
    public function redirectToGoogle(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    /**
     * Callback après l'authentification Google
     */
    #[Route('/api/auth/google/check', name: 'api_auth_google_check', methods: ['GET'])]
    public function googleCheck()
    {
        return new JsonResponse(['message' => 'Authentification Google réussie']);
    }

    /**
     * Redirection vers Facebook pour l'authentification
     */
    #[Route('/api/auth/facebook', name: 'api_auth_facebook', methods: ['GET'])]
    public function redirectToFacebook(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('facebook')->redirect([], []);
    }

    /**
     * Callback après l'authentification Facebook
     */
    #[Route('/api/auth/facebook/check', name: 'api_auth_facebook_check', methods: ['GET'])]
    public function facebookCheck()
    {
        return new JsonResponse(['message' => 'Authentification Facebook réussie']);
    }

    /**
     * Rafraîchir le token JWT
     */
    #[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['GET'])]
    public function refreshToken(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Token manquant'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->jwtSecret, 'HS256'));

            $newPayload = [
                'id' => $decoded->id,
                'email' => $decoded->email,
                'exp' => time() + (60 * 60) //expiration 1h
            ];

            $newToken = JWT::encode($newPayload, $this->jwtSecret, 'HS256');

            return new JsonResponse(['token' => $newToken]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token invalide'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
