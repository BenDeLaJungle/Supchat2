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
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{

    #[Route('/api/users/search', name: 'api_users_search', methods: ['GET'])]
	public function searchUsers(UsersRepository $userRepository, Request $request): JsonResponse {
		$searchTerm = $request->query->get('query');
		if (empty($searchTerm)) {
			return new JsonResponse(['error' => 'Paramètre de recherche manquant'], 400);
		}

		$users = $userRepository->findBySearchTerm($searchTerm);

		$userList = array_map(function ($user) {
			return [
				'id'       => $user->getId(),
				'userName' => $user->getUserName(),
				'firstName'=> $user->getFirstName(),
				'lastName' => $user->getLastName()
			];
		}, $users);

		return new JsonResponse($userList);
	}

    #[Route('/api/users/by-username/{username}', name: 'get_user_by_username', methods: ['GET'])]
		public function getUserByUsername(string $username, UsersRepository $repo): JsonResponse {
		$user = $repo->findOneBy(['userName' => $username]);
		if (!$user) return $this->json(['error' => 'Utilisateur introuvable'], 404);

		return $this->json([
			'id'        => $user->getId(),
			'username'  => $user->getUserName(),
			'email'     => $user->getEmailAddress(),
			'role'      => $user->getRole(),
			'theme'     => $user->getTheme(),
			'status'    => $user->getStatus(),
			'firstName' => $user->getFirstName(),
			'lastName'  => $user->getLastName(),
		]);
	}


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
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName()
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
        $fields = ['firstName', 'lastName', 'userName', 'emailAddress', 'theme', 'status'];

        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $setter = 'set' . ucfirst($field);
                $user->$setter($data[$field]);
            }
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
    public function getAllUsers(UsersRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $userList = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUserName(),
                'email' => $user->getEmailAddress(),
                'role' => $user->getRole(),
                'status' => $user->getStatus(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ];
        }, $users);

        return new JsonResponse($userList);
    }

    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function list(UsersRepository $usersRepository): JsonResponse
    {
        $users = $usersRepository->findAll();

        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUserName()
            ];
        }, $users);

        return $this->json($data);
    }
	#[Route('/api/admin/user/{id}', name: 'admin_user_update', methods: ['PUT'])]
	#[IsGranted('ROLE_ADMIN')]
	public function updateUserAdmin(Request $request, int $id, UsersRepository $userRepository, EntityManagerInterface $em): JsonResponse
	{
		$user = $userRepository->find($id);
		if (!$user) {
			return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
		}

		$data = json_decode($request->getContent(), true);
		if (isset($data['role'])) {
			$user->setRole($data['role']);
		}
		if (isset($data['status'])) {
			$user->setStatus($data['status']);
		}

		$em->flush();

		return new JsonResponse(['message' => 'Utilisateur mis à jour']);
	}
	#[Route('/api/admin/user/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
	#[IsGranted('ROLE_ADMIN')]
	public function deleteUserAdmin(int $id, UsersRepository $userRepository, EntityManagerInterface $em): JsonResponse
	{
		$user = $userRepository->find($id);
		if (!$user) {
			return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
		}

		$em->remove($user);
		$em->flush();

		return new JsonResponse(['message' => 'Utilisateur supprimé']);
	}
}
