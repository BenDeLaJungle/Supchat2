<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private string $jwtSecret;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'default_secret';
    }

    /**
     * Inscription d'un nouvel utilisateur (Authentification classique)
     */
    #[Route('/api/auth/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['firstName'], $data['lastName'], $data['userName'], $data['emailAddress'], $data['password'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['emailAddress' => $data['emailAddress']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], Response::HTTP_CONFLICT);
        }

        $user = new Users();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setUserName($data['userName']);
        $user->setEmailAddress($data['emailAddress']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setRole('ROLE_USER');
        $user->setTheme(true);
        $user->setStatus('online');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Inscription réussie', 'userId' => $user->getId()], Response::HTTP_CREATED);
    }

    /**
     * Connexion utilisateur (Authentification classique)
     */
    #[Route('/api/auth/login', name: 'login', methods: ['POST','OPTIONS'])]
    public function login(Request $request, UsersRepository $usersRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['emailAddress'], $data['password'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], Response::HTTP_BAD_REQUEST);
        }
    
        $user = $usersRepository->findOneBy(['emailAddress' => $data['emailAddress']]);
    
        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return new JsonResponse(['error' => 'Identifiants invalides'], Response::HTTP_UNAUTHORIZED);
        }
    
        // Génération du token d'API
        $payload = [
            'id' => $user->getId(),
            'email' => $user->getEmailAddress(),
            'role' => $user->getRole(),
            'exp' => time() + (60 * 60)
        ];
    
        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');
    
        //Génération du Mercure Token en même temps
        $mercurePayload = [
            'mercure' => [
                'subscribe' => ['/channels/*'], 
            ],
            'exp' => time() + (60 * 60), 
        ];
        $mercureToken = JWT::encode($mercurePayload, $_ENV['MERCURE_JWT_SECRET'], 'HS256');
    
        return new JsonResponse([
            'token' => $token,
            'mercureToken' => $mercureToken,
            'user' => [
                'id' => $user->getId(),
                'userName' => $user->getUserName(),
                'emailAddress' => $user->getEmailAddress(),
                'role' => $user->getRole()
            ]
        ]);
    }
    

    /**
     * Déconnexion utilisateur
     */
    #[Route('/api/auth/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(['message' => 'Déconnexion réussie']);
    }

    /**
     * Rafraîchir un token JWT expiré
     */
    #[Route('/api/auth/refresh', name: 'refresh_token', methods: ['GET'])]
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
                'role' => $decoded->role,
                'exp' => time() + (60 * 60)
            ];

            $newToken = JWT::encode($newPayload, $this->jwtSecret, 'HS256');

            return new JsonResponse(['token' => $newToken]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token invalide'], Response::HTTP_UNAUTHORIZED);
        }
    }

    
}
