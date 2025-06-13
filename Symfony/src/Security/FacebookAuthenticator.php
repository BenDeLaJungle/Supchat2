<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use League\OAuth2\Client\Token\AccessToken;
use Firebase\JWT\JWT;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\FacebookUser;

class FacebookAuthenticator extends OAuth2Authenticator
{
    private string $jwtSecret;
    private EntityManagerInterface $entityManager;
    private ClientRegistry $clientRegistry;

    public function __construct(EntityManagerInterface $entityManager, ClientRegistry $clientRegistry)
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'default_secret';
        $this->entityManager = $entityManager;
        $this->clientRegistry = $clientRegistry;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'api_auth_facebook_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('facebook');

        $accessToken = $client->getAccessToken();

        if (!$accessToken instanceof AccessToken) {
            throw new \LogicException('Le token OAuth2 est invalide.');
        }

        return new SelfValidatingPassport(new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
            return $this->getUserFromFacebook($accessToken, $client);
        }));
    }

    private function getUserFromFacebook(AccessToken $accessToken, OAuth2Client $client): Users
    {
        $facebookUser = $client->fetchUserFromToken($accessToken);

        if (!$facebookUser instanceof FacebookUser) {
            throw new \LogicException('Impossible de récupérer l\'utilisateur Facebook.');
        }

        $email = $facebookUser->getEmail();
        $name = $facebookUser->getName();

        $user = $this->entityManager->getRepository(Users::class)->findOneBy(['emailAddress' => $email]);

        if (!$user) {
            $user = new Users();
            $user->setEmailAddress($email);
            $user->setUserName($name);

            $user->setFirstName($facebookUser->getFirstName() ?? 'Utilisateur');
            $user->setLastName($facebookUser->getLastName() ?? 'Google');

            $user->setOauthProvider('facebook');
            $user->setOauthID($facebookUser->getId());
            $user->setRole('ROLE_USER');
            $user->setTheme(true);
            $user->setStatus('online');

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if (!$user instanceof Users) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = [
            'id' => $user->getId(),
            'email' => $user->getEmailAddress(),
            'exp' => time() + 3600,
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

        $frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';

        return new Response("
        <script>
            window.opener.postMessage({ token: '$jwt' }, '$frontendUrl');
            window.close();
        </script>
        ");
    }

    public function onAuthenticationFailure(Request $request, \Exception $exception): Response
    {
        return new JsonResponse(['error' => 'Échec de l\'authentification Facebook'], Response::HTTP_UNAUTHORIZED);
    }
}
