<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserController extends AbstractController
{
    #[Route('/api/user', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUserName(),
            'email' => $user->getEmailAddress(),
            'role' => $user->getRole(),
            'theme' => $user->getTheme(),
            'status' => $user->getStatus(),
        ]);
    }

    #[Route('/api/user', name: 'api_user_update', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $modified = false;

        if (isset($data['firstName']) && !empty($data['firstName'])) {
            $user->setFirstName($data['firstName']);
            $modified = true;
        }
        if (isset($data['lastName']) && !empty($data['lastName'])) {
            $user->setLastName($data['lastName']);
            $modified = true;
        }
        if (isset($data['userName']) && !empty($data['userName'])) {
            $user->setUserName($data['userName']);
            $modified = true;
        }
        if (isset($data['emailAddress']) && !empty($data['emailAddress'])) {
            $user->setEmailAddress($data['emailAddress']);
            $modified = true;
        }
        if (isset($data['theme'])) {
            $user->setTheme(filter_var($data['theme'], FILTER_VALIDATE_BOOLEAN));
            $modified = true;
        }
        if (isset($data['status']) && !empty($data['status'])) {
            $user->setStatus($data['status']);
            $modified = true;
        }

        if (!$modified) {
            return new JsonResponse(['error' => 'Aucune modification effectuée'], 400);
        }

        $em->flush();
        return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès']);
    }

    #[Route('/api/user', name: 'api_user_delete', methods: ['DELETE'])]
    public function deleteUser(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Compte supprimé avec succès']);
    }

    #[Route('/api/admin/users', name: 'api_admin_users', methods: ['GET'])]
    public function getAllUsers(UsersRepository $userRepository, AuthorizationCheckerInterface $authChecker): JsonResponse
    {
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $users = $userRepository->findAll();
        $userList = [];

        foreach ($users as $user) {
            $userList[] = [
                'id' => $user->getId(),
                'username' => $user->getUserName(),
                'email' => $user->getEmailAddress(),
                'role' => $user->getRole(),
                'status' => $user->getStatus(),
            ];
        }

        return new JsonResponse($userList);
    }

    #[Route('/api/admin/user/{id}', name: 'api_admin_user_update', methods: ['PUT'])]
    public function adminUpdateUser(int $id, Request $request, UsersRepository $userRepository, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): JsonResponse
    {
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $modified = false;

        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
            $modified = true;
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
            $modified = true;
        }
        if (isset($data['userName'])) {
            $user->setUserName($data['userName']);
            $modified = true;
        }
        if (isset($data['emailAddress'])) {
            $user->setEmailAddress($data['emailAddress']);
            $modified = true;
        }
        if (isset($data['role'])) {
            $user->setRole($data['role']);
            $modified = true;
        }
        if (isset($data['status'])) {
            $user->setStatus($data['status']);
            $modified = true;
        }

        if (!$modified) {
            return new JsonResponse(['error' => 'Aucune modification effectuée'], 400);
        }

        $em->flush();
        return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès']);
    }

    #[Route('/api/admin/user/{id}', name: 'api_admin_user_delete', methods: ['DELETE'])]
    public function adminDeleteUser(int $id, UsersRepository $userRepository, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): JsonResponse
    {
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès']);
    }
}
